<?php

/**
 * Sabel_Cookie_InMemory
 *
 * @category   Cookie
 * @package    org.sabel.cookie
 * @author     Ebine Yutaka <ebine.yutaka@sabel.jp>
 * @copyright  2004-2008 Mori Reo <mori.reo@sabel.jp>
 * @license    http://www.opensource.org/licenses/bsd-license.php  BSD License
 */
class Sabel_Cookie_InMemory extends Sabel_Cookie_Abstract
{
  private static $instance = null;
  
  protected $cookies = array();
  
  private function __construct()
  {
    
  }
  
  public static function create()
  {
    if (self::$instance === null) {
      self::$instance = new self();
    }
    
    return self::$instance;
  }
  
  public function set($key, $value, $options = array())
  {
    $options = $this->createOptions($options);
    
    $this->cookies[$key] = array("value"    => urlencode($value),
                                 "expire"   => $options["expire"],
                                 "path"     => $options["path"],
                                 "domain"   => $options["domain"],
                                 "secure"   => $options["secure"],
                                 "httpOnly" => $options["httpOnly"]);
  }
  
  public function rawset($key, $value, $options = array())
  {
    $options = $this->createOptions($options);
    
    $this->cookies[$key] = array("value"    => $value,
                                 "expire"   => $options["expire"],
                                 "path"     => $options["path"],
                                 "domain"   => $options["domain"],
                                 "secure"   => $options["secure"],
                                 "httpOnly" => $options["httpOnly"]);
  }
  
  public function get($key)
  {
    if (array_key_exists($key, $this->cookies)) {
      $cookie = $this->cookies[$key];
      
      if ($cookie["expire"] < time()) {
        return null;
      }
      
      $path = $cookie["path"];
      if ($path === "/") return $cookie["value"];
      
      $uri = $this->getRequestUri();
      if (strpos($uri, $path) === 0) {
        return $cookie["value"];
      }
    }
    
    return null;
  }
  
  protected function getRequestUri()
  {
    if (class_exists("Sabel_Context", false)) {
      $bus = Sabel_Context::getContext()->getBus();
      if (is_object($bus) && ($request = $bus->get("request"))) {
        return "/" . $request->getUri();
      }
    }
    
    if (isset($_SERVER["REQUEST_URI"])) {
      return "/" . normalize_uri($_SERVER["REQUEST_URI"]);
    } else {
      return "/";
    }
  }
}
