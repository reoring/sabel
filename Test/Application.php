<?php

define("LIB_DIR_NAME",        "lib");
define("ADDON_DIR_NAME",      "addon");
define("HELPERS_DIR_NAME",    "helpers");
define("DEFAULT_LAYOUT_NAME", "layout");
define("CONFIG_DIR_PATH",     TEST_APP_DIR . DS . "config");

/**
 * testcase for Sabel Application.
 *
 * @author  Ebine Yutaka <ebine.yutaka@sabel.jp>
 */
class Test_Application extends SabelTestCase
{
  private static $setup = false;
  
  public static function suite()
  {
    return self::createSuite("Test_Application");
  }
  
  public function setUp()
  {
    if (self::$setup) return;
    
    $dir = TEST_APP_DIR . DS . LIB_DIR_NAME . DS . "processor" . DS;
    
    Sabel::fileUsing($dir . "Addon.php", true);
    Sabel::fileUsing($dir . "Session.php", true);
    Sabel::fileUsing($dir . "Executer.php", true);
    Sabel::fileUsing($dir . "Helper.php", true);
    Sabel::fileUsing($dir . "Initializer.php", true);
    Sabel::fileUsing($dir . "Controller.php", true);
    Sabel::fileUsing($dir . "Response.php", true);
    Sabel::fileUsing($dir . "Request.php", true);
    Sabel::fileUsing($dir . "Router.php", true);
    Sabel::fileUsing($dir . "View.php", true);

    unshift_include_paths(array(MODULES_DIR_NAME,
                                LIB_DIR_NAME,
                                MODULES_DIR_NAME . DS . "models",
                                ADDON_DIR_NAME), TEST_APP_DIR . DS);
    
    self::$setup = true;
  }
  
  public function testResponses()
  {
    $bus = $this->getBus("index/index");
    $bus->run(new AppBusConfig());
    
    $responses = $bus->get("response")->getResponses();
    $this->assertEquals("10", $responses["hoge"]);
    $this->assertEquals("20", $responses["fuga"]);
    $this->assertFalse(isset($responses["foo"]));
  }
  
  public function testHtml()
  {
    $bus = $this->getBus("index/hoge");
    $bus->run(new AppBusConfig());
    
    $lines = $this->toHtmlLines($bus->get("result"));
    
    $this->assertEquals("<html>",        $lines[0]);
    $this->assertEquals("<body>",        $lines[1]);
    $this->assertEquals("<p>yamada</p>", $lines[2]);
    $this->assertEquals("</body>",       $lines[3]);
    $this->assertEquals("</html>",       $lines[4]);
  }
  
  public function testInstanceOfController()
  {
    $bus = $this->getBus("");
    $bus->run(new AppBusConfig());
    $this->assertTrue($bus->get("controller") instanceof Index_Controllers_Index);
    
    $bus = $this->getBus("main");
    $bus->run(new AppBusConfig());
    $this->assertTrue($bus->get("controller") instanceof Index_Controllers_Main);
    
    $bus = $this->getBus("manage/login/prepare");
    $bus->run(new AppBusConfig());
    $this->assertTrue($bus->get("controller") instanceof Manage_Controllers_Login);
  }
  
  public function testUriParameters()
  {
    $bus = $this->getBus("manage/index/index/1/2");
    $bus->run(new AppBusConfig());
    $lines = $this->toHtmlLines($bus->get("result"));
    
    $this->assertEquals("<html>", $lines[0]);
    $this->assertEquals("<body>", $lines[1]);
    $this->assertEquals("<h1>manage</h1>",  $lines[2]);
    $this->assertEquals("<p>param1: 1</p>", $lines[3]);
    $this->assertEquals("<p>param2: 2</p>", $lines[4]);
    $this->assertEquals("</body>", $lines[5]);
    $this->assertEquals("</html>", $lines[6]);
    
    $bus = $this->getBus("manage/index/index/100/200");
    $bus->run(new AppBusConfig());
    $lines = $this->toHtmlLines($bus->get("result"));
    
    $this->assertEquals("<p>param1: 100</p>", $lines[3]);
    $this->assertEquals("<p>param2: 200</p>", $lines[4]);
  }
  
  public function testRedirect()
  {
    $bus = $this->getBus("manage/index/index/abcde/2");
    $bus->run(new AppBusConfig());
    
    $controller = $bus->get("controller");
    $this->assertTrue($controller->isRedirected());
    $this->assertEquals("/manage/login/prepare", $controller->getResponse()->getRedirector()->getUri());
  }
  
  public function testNotFound()
  {
    $bus = $this->getBus("manage/hoge/fuga");
    $bus->run(new AppBusConfig());
    
    $response = $bus->get("response");
    $this->assertEquals(Sabel_Response::NOT_FOUND, $response->getStatus()->getCode());
    $headers = $response->outputHeader();
    $this->assertEquals("HTTP/1.0 404 Not Found", $headers[0]);
  }
  
  public function testServerError()
  {
    $bus = $this->getBus("manage/index/refuse");
    $bus->run(new AppBusConfig());
    
    $response = $bus->get("response");
    $this->assertEquals(Sabel_Response::INTERNAL_SERVER_ERROR, $response->getStatus()->getCode());
    $headers = $response->outputHeader();
    $this->assertEquals("HTTP/1.0 500 Internal Server Error", $headers[0]);
  }
  
  public function testInternalRequest()
  {
    $bus = $this->getBus("main/foo");
    $bus->run(new AppBusConfig());
    $this->assertEquals("foo bar", $bus->get("response")->getResponse("bar"));
  }
  
  protected function toHtmlLines($result)
  {
    $html = str_replace(array("\r\n", "\r"), "\n", trim($result));
    return array_map("trim", explode("\n", $html));
  }
  
  protected function getBus($uri)
  {
    $bus = new Sabel_Bus();
    $bus->set("request", new Sabel_Request_Object($uri));
    $bus->set("session", Sabel_Session_InMemory::create());
    return $bus;
  }
}

class AppBusConfig extends Sabel_Bus_Config
{
  protected $processors = array("addon"       => "TestProcessor_Addon",
                                "request"     => "TestProcessor_Request",
                                "response"    => "TestProcessor_Response",
                                "router"      => "TestProcessor_Router",
                                "session"     => "TestProcessor_Session",
                                "controller"  => "TestProcessor_Controller",
                                "helper"      => "TestProcessor_Helper",
                                "initializer" => "TestProcessor_Initializer",
                                "executer"    => "TestProcessor_Executer",
                                "view"        => "TestProcessor_View");
  
  protected $configs = array("map"      => "AppTestMapConfig",
                             "addon"    => "AppTestAddonConfig",
                             "database" => "AppTestDbConfig");
}

class AppTestMapConfig extends Sabel_Map_Configurator
{
  public function configure()
  {
    $this->route("manage")
           ->uri("manage/:controller/:action/:param1/:param2")
           ->module("manage")
           ->defaults(array(":controller" => "index",
                            ":action"     => "index",
                            ":param1"     => null,
                            ":param2"     => null));
    
    $this->route("default")
           ->uri(":controller/:action")
           ->module("index")
           ->defaults(array(":controller" => "index",
                            ":action"     => "index"));
  }
}

class AppBusConfig2 extends AppBusConfig
{
  protected $configs = array("map"      => "AppTestMapConfig2",
                             "addon"    => "AppTestAddonConfig",
                             "database" => "AppTestDbConfig");
}

class AppTestMapConfig2 extends Sabel_Map_Configurator
{
  public function configure()
  {
    $this->route("all")
           ->uri(":uri[]")
           ->module("index")
           ->controller("index")
           ->action("index")
           ->defaults(array(":uri" => array()));
  }
}

class AppTestAddonConfig implements Sabel_Config
{
  public function configure() { return array(); }
}

class AppTestDbConfig implements Sabel_Config
{
  public function configure() { return array(); }
}
