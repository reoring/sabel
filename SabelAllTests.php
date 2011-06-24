<?php

define("TEST_CASE", true);
define("SABEL_BASE", dirname(realpath(__FILE__)));
define("DS", DIRECTORY_SEPARATOR);

define("SBL_LOG_INFO",  0x01);
define("SBL_LOG_DEBUG", 0x02);
define("SBL_LOG_WARN",  0x04);
define("SBL_LOG_ERR",   0x08);
define("SBL_LOG_ALL",   0xFF);
define("SBL_LOG_LEVEL", SBL_LOG_ALL);

if (in_array("-db", $_SERVER["argv"], true)) {
  PHPUnit_Util_Filter::addDirectoryToWhitelist(SABEL_BASE . DS . "sabel" . DS . "db");
} else {
  PHPUnit_Util_Filter::addDirectoryToWhitelist(SABEL_BASE . DS . "sabel");
  PHPUnit_Util_Filter::removeFileFromWhitelist(SABEL_BASE . DS . "sabel" . DS . "Sakle.php");
  PHPUnit_Util_Filter::removeDirectoryFromWhitelist(SABEL_BASE . DS . "sabel" . DS . "db");
  PHPUnit_Util_Filter::removeDirectoryFromWhitelist(SABEL_BASE . DS . "sabel" . DS . "sakle");
  PHPUnit_Util_Filter::removeDirectoryFromWhitelist(SABEL_BASE . DS . "sabel" . DS . "test");
  PHPUnit_Util_Filter::removeDirectoryFromWhitelist(SABEL_BASE . DS . "sabel" . DS . "cookie");
  PHPUnit_Util_Filter::removeFileFromWhitelist(SABEL_BASE . DS . "sabel" . DS . "response" . DS . "Header.php");
}

define("TEST_DATA_DIR", SABEL_BASE . DS . "Test" . DS . "data");
define("TEST_APP_DIR", TEST_DATA_DIR . DS . "application");
define("MODULES_DIR_NAME", "app");
define("VIEW_DIR_NAME",    "views");
define("LOG_DIR_PATH",     TEST_APP_DIR . DS . "logs");
define("MODULES_DIR_PATH", TEST_APP_DIR . DS . "app");
define("COMPILE_DIR_PATH", TEST_APP_DIR . DS . "data" . DS . "compiled");

define("PRODUCTION",  0x01);
define("TEST",        0x02);
define("DEVELOPMENT", 0x04);
define("ENVIRONMENT", TEST);
define("TPL_SUFFIX",  ".tpl");
define("SBL_SECURE_MODE", true);

if (!defined("PHPUnit_MAIN_METHOD")) {
  define("PHPUnit_MAIN_METHOD", "SabelAllTests::main");
}

error_reporting(E_ALL|E_STRICT);

require_once ("PHPUnit/Framework/Test.php");
require_once ("PHPUnit/Framework/Warning.php");
require_once ("PHPUnit/TextUI/TestRunner.php");
require_once ("PHPUnit/Framework/TestCase.php");
require_once ("PHPUnit/Framework/TestSuite.php");
require_once ("PHPUnit/Framework/IncompleteTestError.php");

require_once ("Sabel.php");

require_once ("Test/SabelTestCase.php");

require_once ("Test/Application.php");
require_once ("Test/Object.php");
require_once ("Test/ValueObject.php");
require_once ("Test/Console.php");

require_once ("Test/Annotation.php");
//require_once ("Test/Aspect/Tests.php");
require_once ("Test/Container.php");

require_once ("Test/Exception.php");
require_once ("Test/Bus/Tests.php");
require_once ("Test/Request/Tests.php");
require_once ("Test/Response/Tests.php");
require_once ("Test/Controller/Tests.php");
require_once ("Test/Util/Tests.php");
require_once ("Test/View/Tests.php");
require_once ("Test/Map/Tests.php");
require_once ("Test/Processor/Tests.php");

require_once ("Test/Reflection.php");

require_once ("Test/Cache/Tests.php");
require_once ("Test/Mail/Tests.php");
require_once ("Test/Session/Tests.php");
require_once ("Test/VirtualInheritance.php");
require_once ("Test/DB/TestConfig.php");
require_once ("Test/DB/Statement/Tests.php");
require_once ("Test/DB/Storage/Tests.php");
require_once ("Test/DB/Tests.php");
require_once ("Test/XML/Tests.php");
require_once ("Test/I18n/Gettext.php");
require_once ("Test/Cookie/Tests.php");


class SabelAllTests
{
  public static function main()
  {
    PHPUnit_TextUI_TestRunner::run(self::suite());
  }
  
  public static function suite()
  {
    $suite = new PHPUnit_Framework_TestSuite();
    
    if (in_array("-db", $_SERVER["argv"], true)) {
      $suite->addTest(Test_DB_Statement_Tests::suite());
      $suite->addTest(Test_DB_Storage_Tests::suite());
      $suite->addTest(Test_DB_Tests::suite());
      return $suite;
    }
    
    $suite->addTest(Test_Object::suite());
    $suite->addTest(Test_ValueObject::suite());
    $suite->addTest(Test_Console::suite());
    $suite->addTest(Test_Bus_Tests::suite());
    $suite->addTest(Test_Map_Tests::suite());
    $suite->addTest(Test_Request_Tests::suite());
    $suite->addTest(Test_Response_Tests::suite());
    $suite->addTest(Test_Controller_Tests::suite());
    $suite->addTest(Test_Util_Tests::suite());
    $suite->addTest(Test_View_Tests::suite());
    $suite->addTest(Test_Cache_Tests::suite());
    $suite->addTest(Test_Session_Tests::suite());
    $suite->addTest(Test_Processor_Tests::suite());
    
    $suite->addTest(Test_Annotation::suite());
    $suite->addTest(Test_Reflection::suite());
    $suite->addTest(Test_Container::suite());
    //$suite->addTest(Test_Aspect_Tests::suite());
    $suite->addTest(Test_Exception::suite());
    
    $suite->addTest(Test_I18n_Gettext::suite());
    $suite->addTest(Test_Cookie_Tests::suite());
    $suite->addTest(Test_Mail_Tests::suite());
    $suite->addTest(Test_XML_Tests::suite());
    
    $suite->addTest(Test_Application::suite());
    
    return $suite;
  }
}
