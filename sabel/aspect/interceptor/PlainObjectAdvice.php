<?php

class Sabel_Aspect_Interceptor_PlainObjectAdvice implements Sabel_Aspect_MethodInterceptor
{
  private $advice = null;
  private $adviceMethods = array();
  
  public function __construct($advice)
  {
    $this->advice = $advice;
  }
  
  public function setBeforeAdviceMethod($method)
  {
    $this->adviceMethods["before"] = $method;
  }
  
  public function setAfterAdviceMethod($method)
  {
    $this->adviceMethods["after"] = $method;
  }
  
  public function setAroundAdviceMethod($method)
  {
    $this->adviceMethods["around"] = $method;
  }
  
  public function setThrowsAdviceMethod($method)
  {
    $this->adviceMethods["throws"] = $method;
  }
  
  public function invoke(Sabel_Aspect_MethodInvocation $invocation)
  {
    $advice  = $this->advice;
    $methods = $this->adviceMethods;
    
    $method    = $invocation->getMethod();
    $arguments = $invocation->getArguments();
    $target    = $invocation->getThis();
    
    $hasBefore = isset($methods["before"]);
    $hasAround = isset($methods["around"]);
    $hasAfter  = isset($methods["after"]);
    $hasThrows = isset($methods["throws"]);
    
    try {
      $result = null;
      
      if ($hasBefore) {
        $beforeMethod = $methods["before"];
        $result = $advice->$beforeMethod($method, $arguments, $target);
      }

      if ($result === null && !$hasAround) {
        $result = $invocation->proceed();
      }

      if ($hasAround) {
        $aroundMethod = $methods["around"];
        $result = $advice->$aroundMethod($invocation);
      }
    } catch (Exception $exception) {
      if ($hasThrows) {
        $throwsMethod = $methods["throws"];
        $advice->$throwsMethod($method, $arguments, $target, $exception);
      } else {
        throw $exception;
      }
    }
    
    if ($hasAfter) {
      $afterMethod = $methods["after"];
      
      $advice->$afterMethod($method, $arguments, $target, $result);
    }
    
    return $result;
  }
}