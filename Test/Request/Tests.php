<?php

require_once ("Test/Request/Object.php");

class Test_Request_Tests
{
  public static function suite()
  {
    $suite = new PHPUnit_Framework_TestSuite();
    $suite->addTest(Test_Request_Object::suite());
    
    return $suite;
  }
}
