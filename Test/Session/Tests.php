<?php

require_once("Test/Session/Database.php");

/**
 * @category  Session
 * @author    Ebine Yutaka <ebine.yutaka@sabel.jp>
 */
class Test_Session_Tests
{
  public static function main()
  {
    PHPUnit_TextUI_TestRunner::run(self::suite());
  }
  
  public static function suite()
  {
    $suite = new PHPUnit_Framework_TestSuite();
    
    //$suite->addTest(Test_Session_Database::suite());
    
    return $suite;
  }
}
