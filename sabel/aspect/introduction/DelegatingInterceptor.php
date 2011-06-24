<?php

class Sabel_Aspect_Introduction_DelegatingInterceptor implements Sabel_Aspect_Introduction_Interceptor
{
  public function invoke(Sabel_Aspect_MethodInvocation $invocation)
  {
    return $invocation->proceed();
  }
}