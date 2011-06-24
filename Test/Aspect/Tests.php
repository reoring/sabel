<?php

require_once ("Test/Aspect/Proxy.php");
require_once ("Test/Aspect/Pointcuts.php");
require_once ("Test/Aspect/Matcher.php");
require_once ("Test/Aspect/Introduction.php");
require_once ("Test/Aspect/SimpleUsage.php");

require_once ("Test/Aspect/classes/All.php");
require_once ("Test/Aspect/classes/Interceptors.php");

require_once ("Test/Aspect/Exp.php");

/**
 *
 * @category  Aspect
 * @author    Mori Reo <mori.reo@sabel.jp>
 */
class Test_Aspect_Tests
{
  public static function main()
  {
    PHPUnit_TextUI_TestRunner::run(self::suite());
  }
  
  public static function suite()
  {
    $suite = new PHPUnit_Framework_TestSuite();
    
    $suite->addTest(Test_Aspect_Proxy::suite());
    $suite->addTest(Test_Aspect_Pointcuts::suite());
    $suite->addTest(Test_Aspect_Matcher::suite());
    $suite->addTest(Test_Aspect_Introduction::suite());
    $suite->addTest(Test_Aspect_SimpleUsage::suite());
    
    // @todo remove this before commit.
    $suite->addTest(Test_Aspect_Exp::suite());
    
    return $suite;
  }
}