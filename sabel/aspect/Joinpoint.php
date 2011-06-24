<?php

/**
 * Joinpoint 
 *
 * @interface
 * @category   aspect
 * @package    org.sabel.aspect
 * @author     Mori Reo <mori.reo@sabel.jp>
 * @copyright  2008-2011 Mori Reo <mori.reo@sabel.jp>
 * @license    http://www.opensource.org/licenses/bsd-license.php  BSD License
 * @see org.aopalliance.aop.Joinpoint
 */
interface Sabel_Aspect_Joinpoint
{
  public function getStaticPart();
  public function getThis();
  public function proceed();
}