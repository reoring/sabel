<?php

class Sabel_Aspect_Interceptor_SimpleTrace extends Sabel_Aspect_Interceptor_AbstractTrace
{
  protected function invokeUnderTrace(Sabel_Aspect_MethodInvocation $invocation, $logger)
  {
    $invocationDescription = $this->getInvocationDescription($invocation);
    $logger->trace("Entering: " . $invocationDescription);
    
    try {
      $rval = $invocation->proceed();
      $logger->trace("Exiting: " . $invocationDescription . " with " . var_export($rval, 1));
      return $rval;
    }catch (Exception $ex) {
      $logger->trace("Exception thrown in " . $invocationDescription, $ex);
      throw $ex;
    }
  }
  
  protected function getInvocationDescription($invocation)
  {
    $fmt = "method '%s' of class[%s]";
    return sprintf($fmt, $invocation->getMethod()->getName(),
                         $invocation->getThis()->getName());
  }
}