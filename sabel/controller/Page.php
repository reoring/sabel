<?php

/**
 * abstract controller
 *
 * @category   Controller
 * @package    org.sabel.controller
 * @author     Mori Reo <mori.reo@sabel.jp>
 * @author     Ebine Yutaka <yutaka@ebine.org>
 * @copyright  2004-2008 Mori Reo <mori.reo@sabel.jp>
 * @license    http://www.opensource.org/licenses/bsd-license.php  BSD License
 */
abstract class Sabel_Controller_Page extends Sabel_Object
{
  /**
   * @var Sabel_Request
   */
  protected $request = null;
  
  /**
   * @var Sabel_Response
   */
  protected $response = null;
  
  /**
   * @var Sabel_Response_Redirector
   */
  protected $redirect = null;
  
  /**
   * @var Sabel_Session
   */
  protected $session = null;
  
  /**
   * @var object[]
   */
  protected $mixins = array();
  
  /**
   * @var string
   */
  protected $action = "";
  
  /**
   * @var boolean
   */
  protected $executed = false;
  
  /**
   * @var array
   */
  protected $hidden = array();
  
  /**
   * @var array
   */
  protected $attributes = array();
  
  /**
   * initialize a controller.
   * execute ones before action execute.
   */
  public function initialize()
  {
    
  }
  
  public function finalize()
  {
    
  }
  
  /**
   * @param object $object
   */
  public function mixin($className)
  {
    if (is_string($className)) {
      $instance = new $className();
    } elseif (is_object($className)) {
      $instance  = $className;
      $className = get_class($instance);
    }
    
    if ($instance instanceof self) {
      $properties = array("request", "response", "redirect", "session");
      foreach ($properties as $property) {
        $instance->$property = $this->$property;
      }
    } elseif (method_exists($instance, "setController")) {
      $instance->setController($this);
    }
    
    $reflection = new ReflectionClass($instance);
    $methods = $reflection->getMethods(ReflectionMethod::IS_PUBLIC);
    foreach ($methods as $method) {
      if ($method->getDeclaringClass()->name === $className) {
        $this->mixins[$method->name] = $instance;
      }
    }
  }
  
  /**
   * @param string $method
   * @param array  $arguments
   */
  public function __call($method, $arguments)
  {
    if (isset($this->mixins[$method])) {
      return call_user_func_array(array($this->mixins[$method], $method), $arguments);
    } else {
      $message = "Call to undefined method " . __CLASS__ . "::{$method}()";
      throw new Sabel_Exception_Runtime($message);
    }
  }
  
  /**
   * @param Sabel_Request $request
   *
   * @return void
   */
  public function setRequest(Sabel_Request $request)
  {
    $this->request = $request;
  }
  
  /**
   * @return Sabel_Request
   */
  public function getRequest()
  {
    return $this->request;
  }
  
  /**
   * @param Sabel_Response $response
   *
   * @return void
   */
  public function setResponse(Sabel_Response $response)
  {
    $this->response = $response;
    $this->redirect = $response->getRedirector();
  }
  
  /**
   * @return Sabel_Response
   */
  public function getResponse()
  {
    return $this->response;
  }
  
  /**
   * @param Sabel_Session_Abstract $session
   *
   * @return void
   */
  public function setSession(Sabel_Session_Abstract $session)
  {
    $this->session = $session;
  }
  
  /**
   * @return Sabel_Session_Abstract
   */
  public function getSession()
  {
    return $this->session;
  }
  
  /**
   * execute action
   *
   * @access public
   * @param string $action action method name
   * @return mixed result of execute an action.
   */
  public function execute($action = null, $params = array())
  {
    if ($action === null) {
      $action = $this->action;
    }
    
    if ($this->isReserved($action) || $this->isHiddenAction($action)) {
      $this->response->getStatus()->setCode(Sabel_Response::NOT_FOUND);
    } elseif ($this->isValidAction($action)) {
      if (count($params) >= 1) {
        call_user_func_array(array($this, $action), $params);
      } else {
        $this->$action();
      }
      
      $this->executed = true;
    }
    
    return $this;
  }
  
  public function isExecuted()
  {
    return $this->executed;
  }
  
  public function isRedirected()
  {
    return $this->redirect->isRedirected();
  }
  
  /**
   * @param string $action
   *
   * @return boolean
   */
  protected function isReserved($action)
  {
    static $reserved = array();
    
    if (empty($reserved)) {
      $reserved = get_class_methods(__CLASS__);
    }
    
    return in_array($action, $reserved, true);
  }
  
  /**
   * @param string $action
   *
   * @return boolean
   */
  protected function isHiddenAction($action)
  {
    return in_array($action, $this->hidden, true);
  }
  
  /**
   * @param string $action
   *
   * @return boolean
   */
  protected function isValidAction($action)
  {
    if (!$this->hasMethod($action)) return false;
    
    $method = new ReflectionMethod($this->getName(), $action);
    return $method->isPublic();
  }
  
  public function getAttribute($name)
  {
    if (array_key_exists($name, $this->attributes)) {
      return $this->attributes[$name];
    } else {
      return null;
    }
  }
  
  public function setAttribute($name, $value)
  {
    $this->attributes[$name] = $value;
  }
  
  public function __get($name)
  {
    return $this->getAttribute($name);
  }
  
  public function __set($name, $value)
  {
    $this->setAttribute($name, $value);
  }
  
  public function getAttributes()
  {
    return $this->attributes;
  }
  
  public function setAttributes($attributes)
  {
    $this->attributes = array_merge($this->attributes, $attributes);
  }
  
  public function hasAttribute($name)
  {
    return array_key_exists($name, $this->attributes);
  }
  
  public function isAttributeSet($name)
  {
    return isset($this->attributes[$name]);
  }
  
  public function assign($name, $value)
  {
    $this->response->setResponse($name, $value);
  }
  
  public final function setAction($action)
  {
    if (is_string($action)) {
      $this->action = $action;
    } else {
      $message = __METHOD__ . "() action name must be a string.";
      throw new Sabel_Exception_InvalidArgument($message);
    }
  }
  
  public final function getAction()
  {
    return $this->action;
  }
}
