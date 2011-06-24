<?php

require_once ("Test/Bus/Runner.php");

class Test_Bus_Tests
{
  public static function suite()
  {
    $suite = new PHPUnit_Framework_TestSuite();
    $suite->addTest(Test_Bus_Runner::suite());
    
    return $suite;
  }
}
