<?php

class Sabel_Aspect_Proxy
{
  protected $target = null;
  
  protected $advisor = array();
  
  protected $invocation = null;
  
  protected $checkTargetMethod = true;
  
  public function __construct($targetObject)
  {
    $this->target = $targetObject;
    $this->__setupInvocation();
    
    if (!$this->invocation instanceof Sabel_Aspect_MethodInvocation) {
      throw new Sabel_Exception_Runtime("invocation must be setup");
    }
  }
  
  public function __getTarget()
  {
    return $this->target;
  }
  
  public function __setAdvisor($advisor)
  {
    $this->advisor = $advisor;
  }
  
  public function __checkTargetMethod($check)
  {
    $this->checkTargetMethod = $check;
  }
  
  public function __getClassName()
  {
    return get_class($this->target);
  }
  
  protected function __setupInvocation()
  {
    $this->invocation = new Sabel_Aspect_DefaultMethodInvocation($this, $this->target);
  }
  
  public function __call($method, $arg)
  {
    $reflection = new Sabel_Reflection_Class($this->target);
    
    if ($this->checkTargetMethod && !$reflection->hasMethod($method)) {
      throw new Sabel_Aspect_Exception_MethodNotFound($method . " not found");
    }
    
    $this->invocation->reset($method, $arg);
    
    $advices = array();
    
    $pointcuts = new Sabel_Aspect_DefaultPointcuts();
    
    foreach ($this->advisor as $advisor) {
      $pointcut = $advisor->getPointcut();
      
      if (!$pointcut instanceof Sabel_Aspect_Pointcut)
        throw new Sabel_Exception_Runtime("pointcut must be Sabel_Aspect_Pointcut");
      
      if ($pointcuts->matches($pointcut, $method, $this->target)) {
        $advice = $advisor->getAdvice();
        
        if (is_array($advice)) {
          $advices = array_merge($advice, $advices);
        } else {
          $advices[] = $advice;
        }
      }
    }
    
    if (count($advices) >= 1) {
      $this->invocation->setAdvices($advices);
    }
    
    return $this->invocation->proceed();
  }
}