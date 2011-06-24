<?php

/**
 * MethodInvocation
 *
 * @interface
 * @category   aspect
 * @package    org.sabel.aspect
 * @author     Mori Reo <mori.reo@sabel.jp>
 * @copyright  2008-2011 Mori Reo <mori.reo@sabel.jp>
 * @license    http://www.opensource.org/licenses/bsd-license.php  BSD License
 * @see org.aopalliance.aop.MethodInvocation
 */
interface Sabel_Aspect_MethodInvocation extends Sabel_Aspect_Invocation
{
  /**
   * @return ReflectionMethod
   */
  public function getMethod();
}