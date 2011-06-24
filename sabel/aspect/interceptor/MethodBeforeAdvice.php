<?php

/**
 * MethodBeforeAdvice interceptor
 *
 * @category   aspect
 * @package    org.sabel.aspect
 * @author     Mori Reo <mori.reo@sabel.jp>
 * @copyright  2008-2011 Mori Reo <mori.reo@sabel.jp>
 * @license    http://www.opensource.org/licenses/bsd-license.php  BSD License
 */
class Sabel_Aspect_Interceptor_MethodBeforeAdvice implements Sabel_Aspect_MethodInterceptor
{
  private $interceptor = null;
  
  public function __construct(Sabel_Aspect_Advice_MethodBefore $interceptor)
  {
    $this->interceptor = $interceptor;
  }
  
  /**
   * implements Sabel_Aspect_MethodInterceptor
   */
  public function invoke(Sabel_Aspect_MethodInvocation $i)
  {
    $result = $this->interceptor->before($i->getMethod(), $i->getArguments(), $i->getThis());
    
    if ($result !== null) {
      return $result;
    } else {
      return $i->proceed();
    }
  }
}