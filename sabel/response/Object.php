<?php

/**
 * Sabel_Response_Object
 *
 * @category   Response
 * @package    org.sabel.response
 * @author     Mori Reo <mori.reo@sabel.jp>
 * @copyright  2004-2008 Mori Reo <mori.reo@sabel.jp>
 * @license    http://www.opensource.org/licenses/bsd-license.php  BSD License
 */
class Sabel_Response_Object extends Sabel_Object implements Sabel_Response
{
  protected $httpVersion = "1.0";
  protected $status      = null;
  protected $redirector  = null;
  protected $location    = "";
  protected $headers     = array();
  protected $responses   = array();
  
  protected $statusClass     = "Sabel_Response_Status";
  protected $redirectorClass = "Sabel_Response_Redirector";
  
  public final function __construct()
  {
    $this->setUpStatus();
    $this->setUpRedirector();
  }
  
  public function getStatus()
  {
    return $this->status;
  }
  
  public function getRedirector()
  {
    return $this->redirector;
  }
  
  public function isSuccess()
  {
    return $this->status->isSuccess();
  }
  
  public function isRedirected()
  {
    return ($this->redirector->isRedirected() || $this->status->isRedirect());
  }
  
  public function isFailure()
  {
    return $this->status->isFailure();
  }
  
  public function setHttpVersion($version)
  {
    if (is_string($version)) {
      $this->httpVersion = $version;
    } else {
      $message = __METHOD__ . "() argument must be a string.";
      throw new Sabel_Exception_InvalidArgument($message);
    }
  }
  
  public function getHttpVersion()
  {
    return $this->httpVersion;
  }
  
  public function setResponse($key, $value)
  {
    $this->responses[$key] = $value;
  }
  
  public function getResponse($key)
  {
    if (isset($this->responses[$key])) {
      return $this->responses[$key];
    } else {
      return null;
    }
  }
  
  public function setResponses(array $responses)
  {
    $this->responses = $responses;
  }
  
  public function getResponses()
  {
    return $this->responses;
  }
  
  public function setHeader($key, $value)
  {
    $this->headers[$key] = $value;
  }
  
  public function getHeader($key)
  {
    if (isset($this->headers[$key])) {
      return $this->headers[$key];
    } else {
      return null;
    }
  }
  
  public function getHeaders()
  {
    return $this->headers;
  }
  
  public function hasHeaders()
  {
    return (count($this->headers) !== 0);
  }
  
  public function outputHeader()
  {
    return Sabel_Response_Header::output($this);
  }
  
  public function expiredCache($expire = 31536000)
  {
    $this->setHeader("Expires",       date(DATE_RFC822, time() + $expire) . " GMT");
    $this->setHeader("Last-Modified", date(DATE_RFC822, time() - $expire) . " GMT" );
    $this->setHeader("Cache-Control", "max-age={$expire}");
    $this->setHeader("Pragma", "");
  }
  
  public function setLocation($location)
  {
    $this->location = $location;
    $this->status->setCode(Sabel_Response::FOUND);
    
    return $this;
  }
  
  public function getLocation()
  {
    return $this->location;
  }
  
  protected function setUpStatus()
  {
    $class = $this->statusClass;
    $this->status = new $class();
    
    if (!$this->status instanceof Sabel_Response_Status) {
      $message = __METHOD__ . "() Status object must be an "
               . "instance of Sabel_Response_Status.";
      
      throw new Sabel_Exception_Runtime($message);
    }
  }
  
  protected function setUpRedirector()
  {
    $class = $this->redirectorClass;
    $this->redirector = new $class();
    
    if (!$this->redirector instanceof Sabel_Response_Redirector) {
      $message = __METHOD__ . "() Redirector object must be an "
               . "instance of Sabel_Response_Redirector.";
      
      throw new Sabel_Exception_Runtime($message);
    }
  }
}
