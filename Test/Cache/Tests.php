<?php

require_once("Test/Cache/Test.php");
require_once("Test/Cache/Apc.php");
require_once("Test/Cache/Memcache.php");
require_once("Test/Cache/File.php");
require_once("Test/Cache/Null.php");

/**
 * @category  Cache
 * @author    Ebine Yutaka <ebine.yutaka@sabel.jp>
 */
class Test_Cache_Tests
{
  public static function main()
  {
    PHPUnit_TextUI_TestRunner::run(self::suite());
  }
  
  public static function suite()
  {
    $suite = new PHPUnit_Framework_TestSuite();
    
    //if (extension_loaded("apc")) {
    //  $suite->addTest(Test_Cache_Apc::suite());
    //}
    
    if (extension_loaded("memcache")) {
      $suite->addTest(Test_Cache_Memcache::suite());
    }
    
    $suite->addTest(Test_Cache_File::suite());
    $suite->addTest(Test_Cache_Null::suite());
    
    return $suite;
  }
}
