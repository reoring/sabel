<?php

class Sabel_Aspect_Interceptor_Debug extends Sabel_Aspect_Interceptor_SimpleTrace
{
  public function invoke(Sabel_Aspect_MethodInvocation $invocation)
  {
    return parent::invoke($invocation);
  }
  
  protected function invokeUnderTrace(Sabel_Aspect_MethodInvocation $invocation, $logger)
  {
    $invocationDescription = $this->getInvocationDescription($invocation);
    
    $logger->trace("debug Entering: " . $invocationDescription);
    
    try {
      $s = microtime();
      $rval = $invocation->proceed();
      $end = (microtime() - $s) * 1000;
      $logger->trace("debug Exiting: " . $invocationDescription . " with " . var_export($rval, 1));
      $logger->trace("taking time: " . $end . "ms");
      return $rval;
    }catch (Exception $ex) {
      $logger->trace("Exception thrown in " . $invocationDescription, $ex);
      throw $ex;
    }
  }
}