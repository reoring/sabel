<?php

/**
 * Advisors
 *
 * @category   aspect
 * @package    org.sabel.aspect
 * @author     Mori Reo <mori.reo@sabel.jp>
 * @copyright  2008-2011 Mori Reo <mori.reo@sabel.jp>
 * @license    http://www.opensource.org/licenses/bsd-license.php  BSD License
 */
class Sabel_Aspect_Advisor_StaticMethodMatcher
    extends Sabel_Aspect_Pointcut_StaticMethodMatcher
      implements Sabel_Aspect_Pointcut_Advisor
{
  private $advice = null;
  
  public function setAdvice(Sabel_Aspect_Advice $interceptor)
  {
    $this->advice = $interceptor;
  }
  
  public function getAdvice()
  {
    return $this->advice;
  }
  
  public function isPerInstance()
  {
  }
  
  public function getPointcut()
  {
    return $this;
  }
}