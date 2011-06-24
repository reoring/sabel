<?php

require_once ("Test/Preference/Base.php");
require_once ("Test/Preference/Xml.php");
require_once ("Test/Preference/Database.php");
require_once ("Test/Preference/Memcache.php");

/**
 * test suite for Preference package
 *
 * @category  Preference
 * @author    Mori Reo <mori.reo@sabel.jp>
 */
class Test_Preference_Tests
{
  public static function main()
  {
    PHPUnit_TextUI_TestRunner::run(self::suite());
  }
  
  public static function suite()
  {
    $suite = new PHPUnit_Framework_TestSuite();
    
    $suite->addTest(Test_Preference_Xml::suite());
    $suite->addTest(Test_Preference_Database::suite());
    $suite->addTest(Test_Preference_Memcache::suite());
    
    return $suite;
  }
}