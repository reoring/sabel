<?php

/**
 * Zend Framework
 *
 * An adapter class for Zend_Http_Client based on the curl extension.
 * Curl requires libcurl. See for full requirements the PHP manual: http://php.net/curl
 *
 * @category   Zend
 * @package    Zend_Http
 * @subpackage Client_Adapter
 * @copyright  Copyright (c) 2005-2009 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 */
class Sabel_Http_Client_Adapter_Curl implements Sabel_Http_Client_Adapter_Interface
{
  /**
   * Parameters array
   *
   * @var array
   */
  protected $_config = array();
  
  /**
   * What host/port are we connected to?
   *
   * @var array
   */
  protected $_connected_to = array(null, null);
  
  /**
   * The curl session handle
   *
   * @var resource|null
   */
  protected $_curl = null;
  
  /**
   * List of cURL options that should never be overwritten
   *
   * @var array
   */
  protected $_invalidOverwritableCurlOptions = array(
    CURLOPT_HTTPGET,
    CURLOPT_POST,
    CURLOPT_PUT,
    CURLOPT_CUSTOMREQUEST,
    CURLOPT_HEADER,
    CURLOPT_RETURNTRANSFER,
    CURLOPT_HTTPHEADER,
    CURLOPT_POSTFIELDS,
    CURLOPT_INFILE,
    CURLOPT_INFILESIZE,
    CURLOPT_PORT,
    CURLOPT_MAXREDIRS,
    CURLOPT_CONNECTTIMEOUT,
    CURL_HTTP_VERSION_1_1,
    CURL_HTTP_VERSION_1_0,
  );
  
  /**
   * Response gotten from server
   *
   * @var string
   */
  protected $_response = null;
  
  /**
   * Adapter constructor
   *
   * Config is set using setConfig()
   *
   * @return void
   * @throws Zend_Http_Client_Adapter_Exception
   */
  public function __construct()
  {
    if (!extension_loaded("curl")) {
      $message = __METHOD__ . "() cURL extension has to be loaded to use this Sabel_Http_Client adapter.";
      throw new Sabel_Exception_Runtime($message);
    }
  }
  
  /**
   * Set the configuration array for the adapter
   *
   * @throws Zend_Http_Client_Adapter_Exception
   * @param  Zend_Config | array $config
   * @return Zend_Http_Client_Adapter_Curl
   */
  public function setConfig(array $config = array())
  {
    if(isset($config["proxy_user"]) && isset($config["proxy_pass"])) {
      $this->setCurlOption(CURLOPT_PROXYUSERPWD, $config["proxy_user"] . ":" . $config["proxy_pass"]);
      unset($config["proxy_user"], $config["proxy_pass"]);
    }
    
    foreach ($config as $k => $v) {
      $option = strtolower($k);
      switch($option) {
        case "proxy_host":
          $this->setCurlOption(CURLOPT_PROXY, $v);
          break;
        case "proxy_port":
          $this->setCurlOption(CURLOPT_PROXYPORT, $v);
          break;
        default:
          $this->_config[$option] = $v;
          break;
      }
    }
    
    return $this;
  }
  
  /**
   * Retrieve the array of all configuration options which
   * are not simply passed immediately to CURL extension.
   *
   * @return array
   */
  public function getConfig()
  {
    return $this->_config;
  }
  
  /**
   * Direct setter for cURL adapter related options.
   *
   * @param  string|int $option
   * @param  mixed $value
   * @return Zend_Http_Adapter_Curl
   */
  public function setCurlOption($option, $value)
  {
    if (!isset($this->_config["curloptions"])) {
      $this->_config["curloptions"] = array();
    }
    
    $this->_config["curloptions"][$option] = $value;
    return $this;
  }
  
  /**
   * Initialize curl
   *
   * @param  string  $host
   * @param  int     $port
   * @param  boolean $secure
   * @return void
   * @throws Zend_Http_Client_Adapter_Exception if unable to connect
   */
  public function connect($host, $port = 80, $secure = false)
  {
    // If we're already connected, disconnect first
    if ($this->_curl) {
      $this->close();
    }
    
    // If we are connected to a different server or port, disconnect first
    if ($this->_curl
        && is_array($this->_connected_to)
        && ($this->_connected_to[0] != $host
        || $this->_connected_to[1] != $port)
    ) {
      $this->close();
    }
    
    // Do the actual connection
    $this->_curl = curl_init();
    if ($port != 80) {
      curl_setopt($this->_curl, CURLOPT_PORT, intval($port));
    }
    
    // Set timeout
    curl_setopt($this->_curl, CURLOPT_CONNECTTIMEOUT, $this->_config["timeout"]);
    
    // Set Max redirects
    curl_setopt($this->_curl, CURLOPT_MAXREDIRS, $this->_config["maxredirects"]);
    
    if (!$this->_curl) {
      $this->close();
      
      $message = __METHOD__ . "() Unable to Connect to {$host}:{$port}.";
      throw new Sabel_Exception_Runtime($message);
    }
    
    if ($secure !== false) {
      // Behave the same like Zend_Http_Adapter_Socket on SSL options.
      if (isset($this->_config["sslcert"])) {
        curl_setopt($this->_curl, CURLOPT_SSLCERT, $this->_config["sslcert"]);
      }
      
      if (isset($this->_config["sslpassphrase"])) {
        curl_setopt($this->_curl, CURLOPT_SSLCERTPASSWD, $this->_config["sslpassphrase"]);
      }
    }
    
    // Update connected_to
    $this->_connected_to = array($host, $port);
  }
  
  /**
   * Send request to the remote server
   *
   * @param  string        $method
   * @param  Zend_Uri_Http $uri
   * @param  float         $http_ver
   * @param  array         $headers
   * @param  string        $body
   * @return string        $request
   * @throws Zend_Http_Client_Adapter_Exception If connection fails, connected to wrong host, no PUT file defined, unsupported method, or unsupported cURL option
   */
  public function write($method, $uri, $http_ver = "1.1", $headers = array(), $body = "")
  {
    // Make sure we're properly connected
    if (!$this->_curl) {
      $message = __METHOD__ . "() Trying to write but we are not connected.";
      throw new Sabel_Exception_Runtime($message);
    }
    
    if ($this->_connected_to[0] != $uri->host || $this->_connected_to[1] != $uri->port) {
      $message = __METHOD__ . "() Trying to write but we are connected to the wrong host.";
      throw new Sabel_Exception_Runtime($message);
    }
    
    // set URL
    curl_setopt($this->_curl, CURLOPT_URL, $uri->__toString());
    
    // ensure correct curl call
    $curlValue = true;
    switch ($method) {
      case Sabel_Http_Client::GET:
        $curlMethod = CURLOPT_HTTPGET;
        break;
        
      case Sabel_Http_Client::POST:
        $curlMethod = CURLOPT_POST;
        break;
        
      case Sabel_Http_Client::PUT:
        // There are two different types of PUT request, either a Raw Data string has been set
        // or CURLOPT_INFILE and CURLOPT_INFILESIZE are used.
        if (isset($this->_config["curloptions"][CURLOPT_INFILE])) {
          if (!isset($this->_config["curloptions"][CURLOPT_INFILESIZE])) {
            $message = __METHOD__ . "() Cannot set a file-handle for cURL option CURLOPT_INFILE "
                     . "without also set its size in CURLOPT_INFILESIZE.";
            
            throw new Sabel_Exception_Runtime($message);
          }
          
          // Now we will probably already have Content-Length set, so that we have to delete it
          // from $headers at this point:
          foreach ($headers AS $k => $header) {
            if (stristr($header, "Content-Length:") !== false) {
              unset($headers[$k]);
            }
          }
          
          $curlMethod = CURLOPT_PUT;
        } else {
          $curlMethod = CURLOPT_CUSTOMREQUEST;
          $curlValue = "PUT";
        }
        break;
        
      case Sabel_Http_Client::DELETE:
        $curlMethod = CURLOPT_CUSTOMREQUEST;
        $curlValue = "DELETE";
        break;
        
      case Sabel_Http_Client::OPTIONS:
        $curlMethod = CURLOPT_CUSTOMREQUEST;
        $curlValue = "OPTIONS";
        break;
        
      case Sabel_Http_Client::TRACE:
        $curlMethod = CURLOPT_CUSTOMREQUEST;
        $curlValue = "TRACE";
        break;
        
      default:
        // For now, through an exception for unsupported request methods
        $message = __METHOD__ . "() Method currently not supported.";
        throw new Sabel_Exception_Runtime($message);
    }
    
    // get http version to use
    $curlHttp = ($http_ver = 1.1) ? CURL_HTTP_VERSION_1_1 : CURL_HTTP_VERSION_1_0;
    
    // mark as HTTP request and set HTTP method
    curl_setopt($this->_curl, $curlHttp, true);
    curl_setopt($this->_curl, $curlMethod, $curlValue);
    
    // ensure headers are also returned
    curl_setopt($this->_curl, CURLOPT_HEADER, true);
    
    // ensure actual response is returned
    curl_setopt($this->_curl, CURLOPT_RETURNTRANSFER, true);
    
    // set additional headers
    $headers["Accept"] = "";
    curl_setopt($this->_curl, CURLOPT_HTTPHEADER, $headers);
    
    /**
     * Make sure POSTFIELDS is set after $curlMethod is set:
     * @link http://de2.php.net/manual/en/function.curl-setopt.php#81161
     */
    if ($method == Sabel_Http_Client::POST) {
      curl_setopt($this->_curl, CURLOPT_POSTFIELDS, $body);
    } elseif ($curlMethod == CURLOPT_PUT) {
      // this covers a PUT by file-handle:
      // Make the setting of this options explicit (rather than setting it through the loop following a bit lower)
      // to group common functionality together.
      curl_setopt($this->_curl, CURLOPT_INFILE, $this->_config["curloptions"][CURLOPT_INFILE]);
      curl_setopt($this->_curl, CURLOPT_INFILESIZE, $this->_config["curloptions"][CURLOPT_INFILESIZE]);
      unset($this->_config["curloptions"][CURLOPT_INFILE]);
      unset($this->_config["curloptions"][CURLOPT_INFILESIZE]);
    } elseif ($method == Sabel_Http_Client::PUT) {
      // This is a PUT by a setRawData string, not by file-handle
      curl_setopt($this->_curl, CURLOPT_POSTFIELDS, $body);
    }
    
    // set additional curl options
    if (isset($this->_config["curloptions"])) {
      foreach ((array)$this->_config["curloptions"] as $k => $v) {
        if (!in_array($k, $this->_invalidOverwritableCurlOptions)) {
          if (curl_setopt($this->_curl, $k, $v) == false) {
            $message = __METHOD__ . "() Unknown or erroreous cURL option '%s' set.";
            throw new Sabel_Exception_Runtime(sprintf($message, $k));
          }
        }
      }
    }
    
    // send the request
    $this->_response = curl_exec($this->_curl);
    
    $request  = curl_getinfo($this->_curl, CURLINFO_HEADER_OUT);
    $request .= $body;
    
    if (empty($this->_response)) {
      $message = __METHOD__ . "() Error in cURL request: " . curl_error($this->_curl);
      throw new Sabel_Exception_Runtime($message);
    }
    
    // cURL automatically decodes chunked-messages, this means we have to disallow the Zend_Http_Response to do it again
    if (stripos($this->_response, "Transfer-Encoding: chunked\r\n")) {
      $this->_response = str_ireplace("Transfer-Encoding: chunked\r\n", "", $this->_response);
    }
    
    // Eliminate multiple HTTP responses.
    do {
      $parts = preg_split('|(?:\r?\n){2}|m', $this->_response, 2);
      $again = false;
      
      if (isset($parts[1]) && preg_match("|^HTTP/1\.[01](.*?)\r\n|mi", $parts[1])) {
        $this->_response = $parts[1];
        $again = true;
      }
    } while ($again);
    
    // cURL automatically handles Proxy rewrites, remove the "HTTP/1.0 200 Connection established" string:
    if (stripos($this->_response, "HTTP/1.0 200 Connection established\r\n\r\n") !== false) {
      $this->_response = str_ireplace("HTTP/1.0 200 Connection established\r\n\r\n", "", $this->_response);
    }
    
    return $request;
  }
  
  /**
   * Return read response from server
   *
   * @return string
   */
  public function read()
  {
    return $this->_response;
  }
  
  /**
   * Close the connection to the server
   *
   */
  public function close()
  {
    if (is_resource($this->_curl)) {
      curl_close($this->_curl);
    }
    
    $this->_curl = null;
    $this->_connected_to = array(null, null);
  }

  /**
   * Get cUrl Handle
   *
   * @return resource
   */
  public function getHandle()
  {
    return $this->_curl;
  }
}
