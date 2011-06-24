<?php

class Sabel_Aspect_Weaver
{
  protected static $reflectionCache = array();
    
  protected $target  = null;
  protected $advisor = array();

  protected $advice = null;
  
  protected $advisorClass     = "Sabel_Aspect_Advisor_RegexMatcherPointcut";
  protected $annotatedClass   = ".+";
  protected $interceptorClass = "Sabel_Aspect_Interceptor_PlainObjectAdvice";
  
  protected $methodPatterns = array();
  
  protected $types = array("before", "after", "around", "throws");  
  
  /**
   * default constructer
   *
   * @param $target
   */
  public function __construct($target = null)
  {
    if ($target !== null) {
      $this->target = $target;  
    }
  }
  
  public static function create($target = null)
  {
    return new self($target);
  }
  
  public function getAdvice()
  {
    return $this->advice;
  }
  
  public function build($targetClass, $adviceClasses)
  {
    $this->target = $targetClass;
    
    if (is_array($adviceClasses)) {
      $this->advices = array();
      $adviceClasses = array_reverse($adviceClasses);
      
      foreach ($adviceClasses as $adviceClass) {
        $this->_build($adviceClass);
      }
    } else {
      $this->_build($adviceClasses);
    }
    
    return $this;
  }
  
  private function _build($adviceClass)
  {
    $this->advice = $advice = new $adviceClass();
    
    if (isset(self::$reflectionCache[$adviceClass])) {
      $reflection = self::$reflectionCache[$adviceClass];
    } else {
      $reflection = new Sabel_Reflection_Class($advice);
      self::$reflectionCache[$adviceClass] = $reflection;
    }
            
    $annotatedAdvisor = $reflection->getAnnotation("advisor");
    if ($annotatedAdvisor !== null) {
      $this->advisorClass = $annotatedAdvisor[0][0];
    }
    
    $annotatedInterceptor = $reflection->getAnnotation("interceptor");
    if ($annotatedInterceptor !== null) {
      $this->interceptorClass = $annotatedInterceptor[0][0];
    }
    
    $annotatedClass = $reflection->getAnnotation("classMatch");
    if ($annotatedClass !== null) {
      $this->annotatedClass = $annotatedClass[0][0];
    }
    
    foreach ($reflection->getMethods() as $method) {
      $this->addToAdvisor($method, $advice);
    }
  }
  
  private function addToAdvisor($method, $advice)
  {
    $annotation = $method->getAnnotations();
    
    $type = null;
    foreach ($this->types as $cType) {
      if (isset($annotation[$cType])) {
        $type = $cType;
      }
    }
    if ($type === null) return;
    
    $pattern = $annotation[$type][0][0];
    $methodPattern = "/{$pattern}/";
    
    if (isset($this->methodPatterns[$methodPattern])) {
      $advisor = $this->methodPatterns[$methodPattern];
    } else {
      $advisorClass   = $this->advisorClass;
      $annotatedClass = $this->annotatedClass;
      
      if (!class_exists($advisorClass, true)) {
        throw new Sabel_Exception_ClassNotFound($advisorClass);
      }
      
      $advisor = new $advisorClass();
      $advisor->setClassMatchPattern("/{$annotatedClass}/");
      $advisor->setMethodMatchPattern($methodPattern);
      $this->methodPatterns[$methodPattern] = $advisor;
      $this->addAdvisor($advisor);
    }
    
    $interceptorClass = $this->interceptorClass;
    $poInterceptor = new $interceptorClass($advice);
    
    $setMethod = "set" . ucfirst($type) . "AdviceMethod";
    $poInterceptor->$setMethod($method->getName());
    
    $advisor->addAdvice($poInterceptor);
  }  
  
  public function addAdvisor($advisor, $position = null)
  {
    if ($position === null) {
      $position = count($this->advisor);
    }
    
    $this->advisor[$position] = $advisor;
  }
  
  /**
   * @param object $target
   */
  public function setTarget($target)
  {
    if (class_exists($target)) {
      $this->target = $target;  
    } else {
      throw new Sabel_Exception_Runtime("target must be exist class. {$target} not found");
    }
  }
  
  public function getProxy()
  {
    if ($this->target === null) {
      throw new Sabel_Exception_Runtime("must be set target class");
    }
    
    if (!is_object($this->target)) {
      if (class_exists($this->target, true)) {
        $this->target = new $this->target();
      }
    }
    
    $proxy = new Sabel_Aspect_Proxy($this->target);
    $proxy->__setAdvisor($this->advisor);
    
    return $proxy;
  }
}