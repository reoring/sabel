<?php

/**
 * Zend Framework
 *
 * Zend_Http_Cookie is a class describing an HTTP cookie and all it's parameters. The
 * class also enables validating whether the cookie should be sent to the server in
 * a specified scenario according to the request URI, the expiry time and whether
 * session cookies should be used or not. Generally speaking cookies should be
 * contained in a Cookiejar object, or instantiated manually and added to an HTTP
 * request.
 *
 * See http://wp.netscape.com/newsref/std/cookie_spec.html for some specs.
 *
 * @category   Zend
 * @package    Zend_Http
 * @copyright  Copyright (c) 2005-2009 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 */
class Sabel_Http_Cookie
{
  /**
   * Cookie name
   *
   * @var string
   */
  protected $name;
  
  /**
   * Cookie value
   *
   * @var string
   */
  protected $value;
  
  /**
   * Cookie expiry date
   *
   * @var int
   */
  protected $expires;
  
  /**
   * Cookie domain
   *
   * @var string
   */
  protected $domain;
  
  /**
   * Cookie path
   *
   * @var string
   */
  protected $path;
  
  /**
   * Whether the cookie is secure or not
   *
   * @var boolean
   */
  protected $secure;
  
  /**
   * Cookie object constructor
   *
   * @todo Add validation of each one of the parameters (legal domain, etc.)
   *
   * @param string $name
   * @param string $value
   * @param string $domain
   * @param int $expires
   * @param string $path
   * @param bool $secure
   */
  public function __construct($name, $value, $domain, $expires = null, $path = null, $secure = false)
  {
    if (preg_match("/[=,; \t\r\n\013\014]/", $name)) {
      $message = __METHOD__ . "() Cookie name cannot contain these characters: =,; \\t\\r\\n\\013\\014 ({$name})";
      throw new Sabel_Exception_InvalidArgument($message);
    }
    
    if (!$this->name = (string)$name) {
      $message = __METHOD__ . "() Cookies must have a name.";
      throw new Sabel_Exception_InvalidArgument($message);
    }
    
    if (! $this->domain = (string) $domain) {
      $message = __METHOD__ . "() Cookies must have a domain.";
      throw new Sabel_Exception_InvalidArgument($message);
    }
    
    $this->value = (string)$value;
    $this->expires = ($expires === null ? null : (int)$expires);
    $this->path = ($path ? $path : "/");
    $this->secure = $secure;
  }
  
  /**
   * Get Cookie name
   *
   * @return string
   */
  public function getName()
  {
    return $this->name;
  }
  
  /**
   * Get cookie value
   *
   * @return string
   */
  public function getValue()
  {
    return $this->value;
  }
  
  /**
   * Get cookie domain
   *
   * @return string
   */
  public function getDomain()
  {
    return $this->domain;
  }
  
  /**
   * Get the cookie path
   *
   * @return string
   */
  public function getPath()
  {
    return $this->path;
  }
  
  /**
   * Get the expiry time of the cookie, or null if no expiry time is set
   *
   * @return int|null
   */
  public function getExpiryTime()
  {
    return $this->expires;
  }
  
  /**
   * Check whether the cookie should only be sent over secure connections
   *
   * @return boolean
   */
  public function isSecure()
  {
    return $this->secure;
  }
  
  /**
   * Check whether the cookie has expired
   *
   * Always returns false if the cookie is a session cookie (has no expiry time)
   *
   * @param int $now Timestamp to consider as "now"
   * @return boolean
   */
  public function isExpired($now = null)
  {
    if ($now === null) {
      $now = time();
    }
    
    return (is_int($this->expires) && $this->expires < $now);
  }
  
  /**
   * Check whether the cookie is a session cookie (has no expiry time set)
   *
   * @return boolean
   */
  public function isSessionCookie()
  {
    return ($this->expires === null);
  }
  
  /**
   * Checks whether the cookie should be sent or not in a specific scenario
   *
   * @param string|Zend_Uri_Http $uri URI to check against (secure, domain, path)
   * @param boolean $matchSessionCookies Whether to send session cookies
   * @param int $now Override the current time when checking for expiry time
   * @return boolean
   */
  public function match($uri, $matchSessionCookies = true, $now = null)
  {
    if (is_string($uri)) {
      if (($parsed = parse_url($uri)) !== false) {
        $uri = Sabel_Http_Uri::fromArray($parsed);
      } else {
        $uri = false;
      }
    }
    
    // Make sure we have a valid Zend_Uri_Http object
    if (!$uri && ($uri->scheme === "http" || $uri->scheme === "https")) {
      $message = __METHOD__ . "() Passed URI is not a valid HTTP or HTTPS URI.";
      throw new Sabel_Exception_Runtime('Passed URI is not a valid HTTP or HTTPS URI');
    }
    
    // Check that the cookie is secure (if required) and not expired
    if ($this->secure && $uri->scheme !== "https") {
      return false;
    }
    
    if ($this->isExpired($now)) {
      return false;
    }
    
    if ($this->isSessionCookie() && ! $matchSessionCookies) {
      return false;
    }
    
    // Check if the domain matches
    if (!self::matchCookieDomain($this->getDomain(), $uri->host)) {
      return false;
    }
    
    // Check that path matches using prefix match
    if (!self::matchCookiePath($this->getPath(), $uri->path)) {
      return false;
    }
    
    // If we didn't die until now, return true.
    return true;
  }
  
  /**
   * Get the cookie as a string, suitable for sending as a "Cookie" header in an
   * HTTP request
   *
   * @return string
   */
  public function __toString()
  {
    return $this->name . "=" . urlencode($this->value) . ";";
  }
  
  /**
   * Generate a new Cookie object from a cookie string
   * (for example the value of the Set-Cookie HTTP header)
   *
   * @param string $cookieStr
   * @param Zend_Uri_Http|string $ref_uri Reference URI for default values (domain, path)
   * @return Zend_Http_Cookie A new Zend_Http_Cookie object or false on failure.
   */
  public static function fromString($cookieStr, $ref_uri = null)
  {
    if (is_string($ref_uri)) {
      if (($parsed = parse_url($ref_uri)) !== false) {
        $ref_uri = Sabel_Http_Uri::fromArray($parsed);
      }
    }
    
    $name    = "";
    $value   = "";
    $domain  = "";
    $path    = "";
    $expires = null;
    $secure  = false;
    $parts   = explode(";", $cookieStr);
    
    // If first part does not include '=', fail
    if (strpos($parts[0], "=") === false) {
      return false;
    }
    
    // Get the name and value of the cookie
    list($name, $value) = explode("=", trim(array_shift($parts)), 2);
    $name  = trim($name);
    $value = urldecode(trim($value));
    
    // Set default domain and path
    if ($ref_uri instanceof Sabel_Http_Uri) {
      $domain = $ref_uri->host;
      $path = $ref_uri->path;
      $path = substr($path, 0, strrpos($path, "/"));
    }
    
    // Set other cookie parameters
    foreach ($parts as $part) {
      $part = trim($part);
      
      if (strtolower($part) === "secure") {
        $secure = true;
        continue;
      }
      
      $keyValue = explode("=", $part, 2);
      
      if (count($keyValue) === 2) {
        list($k, $v) = $keyValue;
        switch (strtolower($k))    {
          case "expires":
            if (($expires = strtotime($v)) === false) {
              /**
               * The expiration is past Tue, 19 Jan 2038 03:14:07 UTC
               * the maximum for 32-bit signed integer. Zend_Date
               * can get around that limit.
               *
               * @see Zend_Date
               */
              //require_once 'Zend/Date.php';
              //$expireDate = new Zend_Date($v);
              //$expires = $expireDate->getTimestamp();
            }
            break;

          case "path":
            $path = $v;
            break;

          case "domain":
            $domain = $v;
            break;
        }
      }
    }

    if ($name !== "") {
      return new self($name, $value, $domain, $expires, $path, $secure);
    } else {
      return false;
    }
  }
  
  /**
   * Check if a cookie's domain matches a host name.
   *
   * Used by Zend_Http_Cookie and Zend_Http_CookieJar for cookie matching
   *
   * @param  string $cookieDomain
   * @param  string $host
   *
   * @return boolean
   */
  public static function matchCookieDomain($cookieDomain, $host)
  {
    if (!$cookieDomain) {
      $message = __METHOD__ . "() {$cookieDomain} is expected to be a cookie domain.";
      throw new Sabel_Exception_Runtime($message);
    }
    
    if (!$host) {
      $message = __METHOD__ . "() {$host} is expected to be a host name.";
      throw new Sabel_Exception_Runtime($message);
    }
    
    $cookieDomain = strtolower($cookieDomain);
    $host = strtolower($host);
    
    if ($cookieDomain[0] == ".") {
      $cookieDomain = substr($cookieDomain, 1);
    }
    
    // Check for either exact match or suffix match
    return ($cookieDomain == $host || preg_match('/\.' . $cookieDomain . '$/', $host));
  }
  
  /**
   * Check if a cookie's path matches a URL path
   *
   * Used by Zend_Http_Cookie and Zend_Http_CookieJar for cookie matching
   *
   * @param  string $cookiePath
   * @param  string $path
   * @return boolean
   */
  public static function matchCookiePath($cookiePath, $path)
  {
    if (!$cookiePath) {
      $message = __METHOD__ . "() {$cookiePath} is expected to be a cookie path.";
      throw new Sabel_Exception_Runtime($message);
    }
    
    if (!$path) {
      $message = __METHOD__ . "() {$path} is expected to be a host name.";
      throw new Sabel_Exception_Runtime("\$path is expected to be a host name");
    }
    
    return (strpos($path, $cookiePath) === 0);
  }
}
