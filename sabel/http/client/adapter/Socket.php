<?php

/**
 * Zend Framework
 *
 * @category   Zend
 * @package    Zend_Http
 * @subpackage Client_Adapter
 * @copyright  Copyright (c) 2005-2009 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 */
class Sabel_Http_Client_Adapter_Socket implements Sabel_Http_Client_Adapter_Interface
{
  /**
   * The socket for server connection
   *
   * @var resource|null
   */
  protected $socket = null;
  
  /**
   * What host/port are we connected to?
   *
   * @var array
   */
  protected $connected_to = array(null, null);
  
  /**
   * Parameters array
   *
   * @var array
   */
  protected $config = array(
    "persistent"    => false,
    "ssltransport"  => "ssl",
    "sslcert"       => null,
    "sslpassphrase" => null
  );
  
  /**
   * Request method - will be set by write() and might be used by read()
   *
   * @var string
   */
  protected $method = null;
  
  /**
   * Stream context
   *
   * @var resource
   */
  protected $_context = null;
  
  /**
   * Adapter constructor, currently empty. Config is set using setConfig()
   *
   */
  public function __construct()
  {
    
  }
  
  /**
   * Set the configuration array for the adapter
   *
   * @param Zend_Config | array $config
   */
  public function setConfig(array $config = array())
  {
    foreach ($config as $k => $v) {
      $this->config[strtolower($k)] = $v;
    }
  }
  
  /**
   * Retrieve the array of all configuration options
   *
   * @return array
   */
  public function getConfig()
  {
    return $this->config;
  }
  
  /**
   * Set the stream context for the TCP connection to the server
   *
   * Can accept either a pre-existing stream context resource, or an array
   * of stream options, similar to the options array passed to the
   * stream_context_create() PHP function. In such case a new stream context
   * will be created using the passed options.
   *
   * @since  Zend Framework 1.9
   *
   * @param  mixed $context Stream context or array of context options
   * @return Zend_Http_Client_Adapter_Socket
   */
  public function setStreamContext($context)
  {
    if (is_resource($context) && get_resource_type($context) == "stream-context") {
      $this->_context = $context;
    } elseif (is_array($context)) {
      $this->_context = stream_context_create($context);
    } else {
      $message = __METHOD__ . "() Expecting either a stream context resource or array.";
      throw new Sabel_Exception_InvalidArgument($message);
    }
    
    return $this;
  }
  
  /**
   * Get the stream context for the TCP connection to the server.
   *
   * If no stream context is set, will create a default one.
   *
   * @return resource
   */
  public function getStreamContext()
  {
    if (! $this->_context) {
      $this->_context = stream_context_create();
    }
    
    return $this->_context;
  }
  
  /**
   * Connect to the remote server
   *
   * @param string  $host
   * @param int     $port
   * @param boolean $secure
   */
  public function connect($host, $port = 80, $secure = false)
  {
    // If the URI should be accessed via SSL, prepend the Hostname with ssl://
    $host = ($secure ? $this->config["ssltransport"] : "tcp") . "://" . $host;
    
    // If we are connected to the wrong host, disconnect first
    if (($this->connected_to[0] != $host || $this->connected_to[1] != $port)) {
      if (is_resource($this->socket)) $this->close();
    }
    
    // Now, if we are not connected, connect
    if (!is_resource($this->socket) || !$this->config["keepalive"]) {
      $context = $this->getStreamContext();
      if ($secure) {
        if ($this->config["sslcert"] !== null) {
          if (!stream_context_set_option($context, "ssl", "local_cert", $this->config["sslcert"])) {
            $message = __METHOD__ . "() Unable to set sslcert option.";
            throw new Sabel_Exception_Runtime($message);
          }
        }
        
        if ($this->config["sslpassphrase"] !== null) {
          if (!stream_context_set_option($context, "ssl", "passphrase", $this->config["sslpassphrase"])) {
            $message = __METHOD__ . "() Unable to set sslpassphrase option.";
            throw new Sabel_Exception_Runtime($message);
          }
        }
      }
      
      $flags = STREAM_CLIENT_CONNECT;
      if ($this->config["persistent"]) $flags |= STREAM_CLIENT_PERSISTENT;
      
      $timeout = (int)$this->config["timeout"];
      
      $this->socket = @stream_socket_client(
        $host . ":" . $port, $errno, $errstr, $timeout, $flags, $context
      );
      
      if (!$this->socket) {
        $this->close();
        
        $message = __METHOD__ . "() Unable to Connect to {$host}: {$port}. Error #{$errno}: {$errstr}";
        throw new Sabel_Exception_Runtime($message);
      }
      
      // Set the stream timeout
      if (!stream_set_timeout($this->socket, $timeout)) {
        $method = __METHOD__ . "() Unable to set the connection timeout.";
        throw new Sabel_Exception_Runtime($message);
      }
      
      // Update connected_to
      $this->connected_to = array($host, $port);
    }
  }
  
  /**
   * Send request to the remote server
   *
   * @param string        $method
   * @param Zend_Uri_Http $uri
   * @param string        $http_ver
   * @param array         $headers
   * @param string        $body
   * @return string Request as string
   */
  public function write($method, $uri, $http_ver = "1.1", $headers = array(), $body = "")
  {
    // Make sure we're properly connected
    if (!$this->socket) {
      $message = __METHOD__ . "() Trying to write but we are not connected.";
      throw new Sabel_Exception_Runtime($message);
    }
    
    $host = $uri->host;
    $host = (strtolower($uri->scheme) == "https" ? $this->config["ssltransport"] : "tcp") . "://" . $host;
    
    if ($this->connected_to[0] != $host || $this->connected_to[1] != $uri->port) {
      $message = __METHOD__ . "() Trying to write but we are connected to the wrong host.";
      throw new Sabel_Exception_Runtime($message);
    }
    
    // Save request method for later
    $this->method = $method;
    
    // Build request headers
    $path = $uri->path;
    if ($uri->query) $path .= "?" . $uri->query;
    
    $request = "{$method} {$path} HTTP/{$http_ver}\r\n";
    foreach ($headers as $k => $v) {
      if (is_string($k)) $v = ucfirst($k) . ": {$v}";
      $request .= "{$v}\r\n";
    }
    
    // Add the request body
    $request .= "\r\n" . $body;
    
    // Send the request
    if (!@fwrite($this->socket, $request)) {
      $message = __METHOD__ . "() Error writing request to server.";
      throw new Sabel_Exception_Runtime('Error writing request to server');
    }
    
    return $request;
  }
  
  /**
   * Read response from server
   *
   * @return string
   */
  public function read()
  {
    // First, read headers only
    $response = "";
    $gotStatus = false;
    
    while (($line = @fgets($this->socket)) !== false) {
      $gotStatus = $gotStatus || (strpos($line, "HTTP") !== false);
      if ($gotStatus) {
        $response .= $line;
        if (rtrim($line) === "") break;
      }
    }
    
    $this->_checkSocketReadTimeout();
    
    $statusCode = Sabel_Http_Response::extractCode($response);
    
    // Handle 100 and 101 responses internally by restarting the read again
    if ($statusCode == 100 || $statusCode == 101) {
      return $this->read();
    }
    
    // Check headers to see what kind of connection / transfer encoding we have
    $headers = Sabel_Http_Response::extractHeaders($response);
    
    /**
     * Responses to HEAD requests and 204 or 304 responses are not expected
     * to have a body - stop reading here
     */
    if ($statusCode == 304 || $statusCode == 204 || $this->method === Sabel_Http_Client::HEAD) {
      // Close the connection if requested to do so by the server
      if (isset($headers["connection"]) && $headers["connection"] === "close") {
        $this->close();
      }
      
      return $response;
    }
    
    // If we got a 'transfer-encoding: chunked' header
    if (isset($headers["transfer-encoding"])) {
      if (strtolower($headers["transfer-encoding"]) === "chunked") {
        do {
          $line = @fgets($this->socket);
          $this->_checkSocketReadTimeout();
          
          $chunk = $line;
          
          // Figure out the next chunk size
          $chunksize = trim($line);
          if (!ctype_xdigit($chunksize)) {
            $this->close();
            
            $message = __METHOD__ . "() Unable to read chunk body.";
            throw new Sabel_Exception_Runtime($message);
          }
          
          // Convert the hexadecimal value to plain integer
          $chunksize = hexdec($chunksize);
          
          // Read next chunk
          $read_to = ftell($this->socket) + $chunksize;
          
          do {
            $current_pos = ftell($this->socket);
            if ($current_pos >= $read_to) break;
            
            $line = @fread($this->socket, $read_to - $current_pos);
            if ($line === false || strlen($line) === 0) {
              $this->_checkSocketReadTimeout();
              break;
            } else {
              $chunk .= $line;
            }
          } while (!feof($this->socket));
          
          $chunk .= @fgets($this->socket);
          $this->_checkSocketReadTimeout();
          $response .= $chunk;
        } while ($chunksize > 0);
      } else {
        $this->close();
        
        $message = __METHOD__ . "() Cannot handle '{$headers['transfer-encoding']}' transfer encoding. ";
        throw new Sabel_Exception_Runtime($message);
      }
    } elseif (isset($headers["content-length"])) {
      $current_pos = ftell($this->socket);
      $chunk = "";
      
      for ($read_to = $current_pos + $headers["content-length"]; $read_to > $current_pos; $current_pos = ftell($this->socket)) {
        $chunk = @fread($this->socket, $read_to - $current_pos);
        if ($chunk === false || strlen($chunk) === 0) {
          $this->_checkSocketReadTimeout();
          break;
        }
        
        $response .= $chunk;
        
        // Break if the connection ended prematurely
        if (feof($this->socket)) break;
      }
    } else {
      do {
        $buff = @fread($this->socket, 8192);
        if ($buff === false || strlen($buff) === 0) {
          $this->_checkSocketReadTimeout();
          break;
        } else {
          $response .= $buff;
        }
      } while (feof($this->socket) === false);
      
      $this->close();
    }
    
    // Close the connection if requested to do so by the server
    if (isset($headers["connection"]) && $headers["connection"] === "close") {
      $this->close();
    }
    
    return $response;
  }
  
  /**
   * Close the connection to the server
   *
   */
  public function close()
  {
    if (is_resource($this->socket)) {
      @fclose($this->socket);
    }
    
    $this->socket = null;
    $this->connected_to = array(null, null);
  }
  
  /**
   * Check if the socket has timed out - if so close connection and throw
   * an exception
   *
   * @throws Zend_Http_Client_Adapter_Exception with READ_TIMEOUT code
   */
  protected function _checkSocketReadTimeout()
  {
    if ($this->socket) {
      $info = stream_get_meta_data($this->socket);
      $timedout = $info["timed_out"];
      
      if ($timedout) {
        $this->close();
        
        $message = __METHOD__ . "() Read timed out after {$this->config['timeout']} seconds";
        throw new Sabel_Exception_Runtime($message);
      }
    }
  }
  
  /**
   * Destructor: make sure the socket is disconnected
   *
   * If we are in persistent TCP mode, will not close the connection
   *
   */
  public function __destruct()
  {
    if (!$this->config["persistent"] && $this->socket) {
      $this->close();
    }
  }
}
