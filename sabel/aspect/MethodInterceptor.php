<?php

/**
 * @see org.aopalliance.aop.MethodInterceptor
 */
interface Sabel_Aspect_MethodInterceptor extends Sabel_Aspect_Interceptor
{
  public function invoke(Sabel_Aspect_MethodInvocation $invocation);
}