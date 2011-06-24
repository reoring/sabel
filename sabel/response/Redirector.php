<?php

/**
 * Sabel_Response_Redirector
 *
 * @category   Response
 * @package    org.sabel.core
 * @author     Mori Reo <mori.reo@sabel.jp>
 * @copyright  2004-2008 Mori Reo <mori.reo@sabel.jp>
 * @license    http://www.opensource.org/licenses/bsd-license.php  BSD License
 */
class Sabel_Response_Redirector extends Sabel_Object
{
  /**
   * @var string
   */
  protected $url = "";
  
  /**
   * @var string
   */
  protected $uri = "";
  
  /**
   * @var boolean
   */
  protected $redirected = false;
  
  /**
   * @var array
   */
  protected $parameters = array();
  
  /**
   * @var string
   */
  protected $flagment = "";
  
  /**
   * @return boolean
   */
  public function isRedirected()
  {
    return $this->redirected;
  }
  
  /**
   * @param string $url
   *
   * @return void
   */
  public function url($url)
  {
    $this->url = $url;
    $this->redirected = true;
  }
  
  /**
   * @return string
   */
  public function getUrl()
  {
    return $this->url;
  }
  
  /**
   * HTTP Redirect to another location with uri.
   *
   * @param string $uri
   * @param array  $parameters
   * @param string $flagment
   *
   * @return string
   */
  public function to($uri, $parameters = array(), $flagment = "")
  {
    $context = Sabel_Context::getContext();
    
    return $this->uri($context->getCandidate()->uri($uri), $parameters, $flagment);
  }
  
  /**
   * @param string $uri
   *
   * @return void
   */
  public function uri($uri, $parameters = array(), $flagment = "")
  {
    $this->uri = "/" . ltrim($uri, "/");
    
    $this->setParameters($parameters);
    $this->setFlagment($flagment);
    
    $this->redirected = true;
    
    return $this->uri;
  }
  
  /**
   * @return string
   */
  public function getUri($withParameter = true, $withFlagment = true)
  {
    $uri = $this->uri;
    
    if ($withParameter && !empty($this->parameters)) {
      $uri .= "?" . http_build_query($this->parameters, "", "&");
    }
    
    if ($withFlagment && $this->flagment !== "") {
      $uri .= "#{$this->flagment}";
    }
    
    return $uri;
  }
  
  /**
   * @param array $parameters
   *
   * @return self
   */
  public function setParameters(array $parameters)
  {
    $this->parameters = $parameters;
    
    return $this;
  }
  
  /**
   * @param string $key
   * @param mixed  $value
   *
   * @return self
   */
  public function addParameter($key, $value)
  {
    $this->parameters[$key] = $value;
    
    return $this;
  }
  
  /**
   * @return boolean
   */
  public function hasParameters()
  {
    return (count($this->parameters) > 0);
  }
  
  /**
   * @param string $key
   *
   * @return self
   */
  public function deleteParameter($key)
  {
    unset($this->parameters[$key]);
    
    return $this;
  }
  
  /**
   * @return self
   */
  public function clearParameters()
  {
    $this->parameters = array();
    
    return $this;
  }
  
  /**
   * @param string $flagment
   *
   * @return self
   */
  public function setFlagment($flagment)
  {
    $this->flagment = $flagment;
    
    return $this;
  }
  
  /**
   * @return string
   */
  public function getFlagment()
  {
    return $this->flagment;
  }
}
