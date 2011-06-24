<?php

require_once ("Test/Processor/Abstract.php");
require_once ("Test/Processor/Request.php");
require_once ("Test/Processor/Router.php");
require_once ("Test/Processor/Addon.php");
require_once ("Test/Processor/Controller.php");
require_once ("Test/Processor/Response.php");

define("PROCESSORS_DIR", "generator" . DS . "skeleton" . DS . "en" . DS . "lib" . DS . "processor");

class Test_Processor_Tests
{
  public static function suite()
  {
    $suite = new PHPUnit_Framework_TestSuite();
    $suite->addTest(Test_Processor_Request::suite());
    $suite->addTest(Test_Processor_Router::suite());
    $suite->addTest(Test_Processor_Addon::suite());
    $suite->addTest(Test_Processor_Controller::suite());
    $suite->addTest(Test_Processor_Response::suite());
    
    return $suite;
  }
}

class TestMapConfig extends Sabel_Map_Configurator
{
  public function configure()
  {
    $this->route("devel")
           ->uri("devel/:controller/:action/:param")
           ->module("devel")
           ->defaults(array(":controller" => "main",
                            ":action"     => "index",
                            ":param"      => null));
    
    $this->route("default")
           ->uri(":controller/:action")
           ->module("index")
           ->defaults(array(":controller" => "index",
                            ":action"     => "index"));
  }
}

class TestAddonConfig implements Sabel_Config
{
  public function configure()
  {
    $addons = array();
    $addons[] = "hoge";
    $addons[] = "fuga";
    
    return $addons;
  }
}
