<?php

/**
 * test case of sabel Preference package
 *
 * @abstract
 * @category   Test
 * @package    org.sabel.preference
 * @author     Mori Reo <mori.reo@sabel.jp>
 * @copyright  2004-2008 Mori Reo <mori.reo@sabel.jp>
 * @license    http://www.opensource.org/licenses/bsd-license.php  BSD License
 */ 
class Test_Preference extends SabelTestCase
{
  public static function suite()
  {
    return self::createSuite("Test_Preference");
  }
  
  /**
   * set up
   *
   * @access public
   * @return void
   */
  public function setUp()
  {
  }
}