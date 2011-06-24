<?php

require_once ("Test/Response/Object.php");
require_once ("Test/Response/Redirector.php");
require_once ("Test/Response/Header.php");

class Test_Response_Tests
{
  public static function suite()
  {
    $suite = new PHPUnit_Framework_TestSuite();
    $suite->addTest(Test_Response_Object::suite());
    $suite->addTest(Test_Response_Redirector::suite());
    $suite->addTest(Test_Response_Header::suite());
    
    return $suite;
  }
}
