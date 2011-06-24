<?php

require_once ("Test/Mail/MimeDecode.php");

class Test_Mail_Tests
{
  public static function suite()
  {
    $suite = new PHPUnit_Framework_TestSuite();
    $suite->addTest(Test_Mail_MimeDecode::suite());
    
    return $suite;
  }
}
