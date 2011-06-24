<?php

/**
 * Sabel_Addon
 *
 * @interface
 * @category   Addon
 * @package    org.sabel.addon
 * @author     Ebine Yutaka <ebine.yutaka@sabel.jp>
 * @copyright  2004-2008 Mori Reo <mori.reo@sabel.jp>
 * @license    http://www.opensource.org/licenses/bsd-license.php  BSD License
 */
interface Sabel_Addon
{
  /**
   * @param Sabel_Bus $bus
   *
   * @return void
   */
  public function execute(Sabel_Bus $bus);
}
