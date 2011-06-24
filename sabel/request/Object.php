<?php

/**
 * Sabel_Request_Object
 *
 * @category   Request
 * @package    org.sabel.request
 * @author     Mori Reo <mori.reo@sabel.jp>
 * @copyright  2004-2008 Mori Reo <mori.reo@sabel.jp>
 * @license    http://www.opensource.org/licenses/bsd-license.php  BSD License
 */
class Sabel_Request_Object extends Sabel_Object implements Sabel_Request
{
  /**
   * @var string
   */
  private $uri = "";
  
  /**
   * @var const Sabel_Request
   */
  private $method = Sabel_Request::GET;
  
  /**
   * @var array
   */
  private $getValues = array();
  
  /**
   * @var array
   */
  private $postValues = array();
  
  /**
   * @var array
   */
  private $parameterValues = array();
  
  /**
   * @var Sabel_Request_File[]
   */
  private $files = array();
  
  /**
   * @var array
   */
  private $httpHeaders = array();
  
  public function __construct($uri = "")
  {
    $this->setUri($uri);
  }
  
  public function setUri($uri)
  {
    $this->uri = ltrim($uri, "/");
    
    return $this;
  }
  
  public function getUri($withQuery = false)
  {
    $uri = $this->uri;
    
    if ($withQuery && ($values = $this->fetchGetValues())) {
      $uri .= "?" . http_build_query($values);
    }
    
    return $uri;
  }
  
  /**
   * get request
   *
   * @param string $uri
   */
  public function get($uri)
  {
    return $this->method(Sabel_Request::GET)->setUri($uri);
  }
  
  /**
   * post request
   *
   * @param string $uri
   */
  public function post($uri)
  {
    return $this->method(Sabel_Request::POST)->setUri($uri);
  }
  
  /**
   * put request
   *
   * @param string $uri
   */
  public function put($uri)
  {
    return $this->method(Sabel_Request::PUT)->setUri($uri);
  }
  
  /**
   * delete request
   *
   * @param string $uri
   */
  public function delete($uri)
  {
    return $this->method(Sabel_Request::DELETE)->setUri($uri);
  }
  
  public function method($method)
  {
    $this->method = $method;
    
    return $this;
  }
  
  public function isPost()
  {
    return ($this->method === Sabel_Request::POST);
  }
  
  public function isGet()
  {
    return ($this->method === Sabel_Request::GET);
  }
  
  public function isPut()
  {
    return ($this->method === Sabel_Request::PUT);
  }
  
  public function isDelete()
  {
    return ($this->method === Sabel_Request::DELETE);
  }
  
  public function getMethod()
  {
    return $this->method;
  }
  
  public function value($key, $value)
  {
    switch ($this->method) {
      case (Sabel_Request::GET):
        $this->setGetValue($key, $value);
        break;
      case (Sabel_Request::POST):
        $this->setPostValue($key, $value);
        break;
    }
    
    return $this;
  }
  
  public function values(array $lists)
  {
    if ($this->isPost()) {
      $this->setPostValues(array_merge($this->postValues, $lists));
    } else {
      $this->setGetValues(array_merge($this->getValues, $lists));
    }
    
    return $this;
  }
  
  public function hasValueWithMethod($name)
  {
    if ($this->isPost()) {
      return ($this->hasPostValue($name));
    } elseif ($this->isGet()) {
      return ($this->hasGetValue($name));
    }
  }
  
  public function getValueWithMethod($name)
  {
    if ($this->hasValueWithMethod($name)) {
      if ($this->isPost()) {
        return $this->fetchPostValue($name);
      } elseif ($this->isGet()) {
        return $this->fetchGetValue($name);
      }
    } else {
      return null;
    }
  }
  
  public function setGetValue($key, $value)
  {
    $this->getValues[$key] = ($value === "") ? null : $value;
  }
  
  public function setGetValues(array $values)
  {
    $this->getValues = $this->toNull($values);
  }
  
  public function fetchGetValues()
  {
    return $this->getValues;
  }
  
  public function hasGetValue($name)
  {
    return ($this->fetchGetValue($name) !== null);
  }
  
  public function isGetSet($name)
  {
    return array_key_exists($name, $this->getValues);
  }
  
  public function fetchGetValue($key)
  {
    return (isset($this->getValues[$key])) ? $this->getValues[$key] : null;
  }
  
  public function setPostValue($key, $value)
  {
    $this->postValues[$key] = ($value === "") ? null : $value;
  }
  
  public function setPostValues(array $values)
  {
    $this->postValues = $this->toNull($values);
  }
  
  public function hasPostValue($name)
  {
    return ($this->fetchPostValue($name) !== null);
  }
  
  public function isPostSet($name)
  {
    return array_key_exists($name, $this->postValues);
  }
  
  public function fetchPostValue($key)
  {
    return (isset($this->postValues[$key])) ? $this->postValues[$key] : null;
  }
  
  public function fetchPostValues()
  {
    return $this->postValues;
  }
  
  public function setParameterValue($key, $value)
  {
    $this->parameterValues[$key] = ($value === "") ? null : $value;
  }
  
  public function setParameterValues(array $values)
  {
    $this->parameterValues = $this->toNull($values);
  }
  
  public function fetchParameterValue($key)
  {
    return (isset($this->parameterValues[$key])) ? $this->parameterValues[$key] : null;
  }
  
  public function fetchParameterValues()
  {
    return $this->parameterValues;
  }
  
  public function setFile($name, $file)
  {
    if ($file instanceof Sabel_Request_File) {
      $this->files[$name] = $file;
    } else {
      $message = __METHOD__ . "() argument 2 must be an instance of Sabel_Request_File.";
      throw new Sabel_Exception_Runtime($message);
    }
  }
  
  public function setFiles(array $files)
  {
    foreach ($files as $name => $file) {
      $this->setFile($name, $file);
    }
  }
  
  public function hasFile($name)
  {
    return ($this->getFile($name) !== null);
  }
  
  public function isFileSet($name)
  {
    return array_key_exists($name, $this->files);
  }
  
  public function getFile($key)
  {
    return (isset($this->files[$key])) ? $this->files[$key] : null;
  }
  
  public function getFiles()
  {
    return $this->files;
  }
  
  public function find($key)
  {
    if (empty($key)) return null;
    
    $result = null;
    $values = array(
      $this->fetchPostValues(),
      $this->fetchGetValues(),
      $this->fetchParameterValues()
    );
    
    foreach ($values as $value) {
      if (isset($value[$key])) {
        if ($result !== null) {
          $message = __METHOD__ . "() request key overlaps.";
          throw new Sabel_Exception_Runtime($message);
        } else {
          $result = $value[$key];
        }
      }
    }
    
    return ($result === "") ? null : $result;
  }
  
  public function setHttpHeaders(array $headers)
  {
    $this->httpHeaders = $headers;
  }
  
  public function getHttpHeader($name)
  {
    $key = "HTTP_" . strtoupper(str_replace("-", "_", $name));
    return (isset($this->httpHeaders[$key])) ? $this->httpHeaders[$key] : null;
  }
  
  public function getHttpHeaders()
  {
    return $this->httpHeaders;
  }
  
  public function getExtension()
  {
    $parts = explode("/", $this->uri);
    $lastPart = array_pop($parts);
    
    if (($pos = strpos($lastPart, ".")) === false) {
      return "";
    } else {
      return substr($lastPart, $pos + 1);
    }
  }
  
  protected function toNull($values)
  {
    foreach ($values as $key => $value) {
      if (is_array($value)) {
        $values[$key] = $this->toNull($value);
      } elseif ($value === "") {
        $values[$key] = null;
      }
    }
    
    return $values;
  }
}
