<?php

require_once ("Test/Map/Match.php");
require_once ("Test/Map/Destination.php");

class Test_Map_Tests
{
  public static function suite()
  {
    $suite = new PHPUnit_Framework_TestSuite();
    $suite->addTest(Test_Map_Match::suite());
    $suite->addTest(Test_Map_Destination::suite());
    
    return $suite;
  }
}
