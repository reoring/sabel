<?php

/**
 * preference store to XML
 *
 * @abstract
 * @category   Preference
 * @package    org.sabel.preference
 * @author     Mori Reo <mori.reo@sabel.jp>
 * @copyright  2004-2008 Mori Reo <mori.reo@sabel.jp>
 * @license    http://www.opensource.org/licenses/bsd-license.php  BSD License
 */

class Test_Preference_Xml extends Test_Preference_Base
{
  public static function suite()
  {
    return self::createSuite("Test_Preference_Xml");
  }

  /**
   * set up
   *
   * @access public
   * @return void
   */
  public function setUp()
  {
    $files = array("/tmp/data/preferences/default.xml",
                   "/tmp/data/preferences/test.xml");

    foreach ($files as $file) {
      if (is_readable($file)) {
        unlink($file);
      }
    }

    $this->pref = Sabel_Preference::create();
  }

  public function testNotConfigurationFileSpecified()
  {
    $preference = Sabel_Preference::create();
    $this->assertTrue(is_readable("/tmp/data/preferences/default.xml"));
  }

  public function testNotSpecifyFile()
  {
    $preference = Sabel_Preference::create(new __PrefConfigNotSpecifyFile());
    $this->assertTrue(is_readable("/tmp/data/preferences/default.xml"));
  }

  public function testConfig()
  {
    $preference = Sabel_Preference::create(new __PrefConfig());
    $this->assertTrue(is_readable("/tmp/data/preferences/specified.xml"));
  }

  public function testConfigNoDot()
  {
    $preference = Sabel_Preference::create(new __PrefConfigNoDot());
    $this->assertTrue(is_readable("/tmp/data/preferences/test.xml"));
  }
}

class __PrefConfigNotSpecifyFile implements Sabel_Config
{
  public function configure()
  {
    return array("backend" => "Sabel_Preference_Xml");
  }
}

class __PrefConfig implements Sabel_Config
{
  public function configure()
  {
    return array("backend" => "Sabel_Preference_Xml", "file" => "specified.xml");
  }
}

class __PrefConfigNoDot implements Sabel_Config
{
  public function configure()
  {
    return array("backend" => "Sabel_Preference_Xml",
                 "file" => "test");
  }
}
