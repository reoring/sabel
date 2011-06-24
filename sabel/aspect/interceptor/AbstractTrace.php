<?php

abstract class Sabel_Aspect_Interceptor_AbstractTrace implements Sabel_Aspect_MethodInterceptor
{
  public function invoke(Sabel_Aspect_MethodInvocation $invocation)
  {
    $logger = new Sabel_Aspect_Interceptor_Logger();
    return $this->invokeUnderTrace($invocation, $logger);
  }
  
  abstract protected function invokeUnderTrace(Sabel_Aspect_MethodInvocation $invocation, $logger);
}