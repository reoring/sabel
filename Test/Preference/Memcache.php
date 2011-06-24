<?php

/**
 * preference store to a database
 *
 * @abstract
 * @category   Preference
 * @package    org.sabel.preference
 * @author     Mori Reo <mori.reo@sabel.jp>
 * @copyright  2004-2008 Mori Reo <mori.reo@sabel.jp>
 * @license    http://www.opensource.org/licenses/bsd-license.php  BSD License
 */
class Test_Preference_Memcache extends Test_Preference_Base
{
  public static function suite()
  {
    return self::createSuite("Test_Preference_Memcache");
  }

  /**
   * set up
   *
   * @access public
   * @return void
   */
  public function setUp()
  {
    $this->pref = Sabel_Preference::create(new __Preference_Memcache_Config());
  }

  public function tearDown()
  {
    $memcache = new Memcache();
    $memcache->addServer("localhost", 11211);
    $memcache->flush();
  }
}

class __Preference_Memcache_Config implements Sabel_Config
{
  public function configure()
  {
    return array("backend" => "Sabel_Preference_Memcache");
  }
}
