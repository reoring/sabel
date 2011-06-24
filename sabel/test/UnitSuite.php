<?php

/**
 * Sabel_Test_UnitSuite
 *
 * @category   Test
 * @package    org.sabel.test
 * @author     Ebine Yutaka <yutaka@ebine.org>
 * @copyright  2004-2008 Mori Reo <mori.reo@sabel.jp>
 * @license    http://www.opensource.org/licenses/bsd-license.php  BSD License
 */
class Sabel_Test_UnitSuite extends Sabel_Test_TestSuite
{
  public function add($testName)
  {
    parent::add("Unit_" . $testName);
  }
}
