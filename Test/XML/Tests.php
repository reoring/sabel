<?php

require_once ("Test/XML/Test.php");

class Test_XML_Tests
{
  public static function suite()
  {
    define("XML_TEST_DIR", dirname(__FILE__));
    
    $suite = new PHPUnit_Framework_TestSuite();
    //$suite->addTest(Test_XML_Element::suite());
    //$suite->addTest(Test_XML_Elements::suite());
    $suite->addTest(Test_XML_Test::suite());
    
    return $suite;
  }
}
