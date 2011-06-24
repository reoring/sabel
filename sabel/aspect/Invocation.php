<?php

/**
 * Invocation
 *
 * @interface
 * @category   aspect
 * @package    org.sabel.aspect
 * @author     Mori Reo <mori.reo@sabel.jp>
 * @copyright  2008-2011 Mori Reo <mori.reo@sabel.jp>
 * @license    http://www.opensource.org/licenses/bsd-license.php  BSD License
 * @see org.aopalliance.aop.Invocation
 */
interface Sabel_Aspect_Invocation extends Sabel_Aspect_Joinpoint
{
  /**
   * @return array
   */
  public function getArguments();
}