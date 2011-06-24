<?php

require_once ("Test/Cookie/InMemory.php");

class Test_Cookie_Tests
{
  public static function suite()
  {
    $suite = new PHPUnit_Framework_TestSuite();
    $suite->addTest(Test_Cookie_InMemory::suite());
    
    return $suite;
  }
}
