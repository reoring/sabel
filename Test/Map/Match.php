<?php

/**
 * TestCase for sabel.map.*
 *
 * @category  Map
 * @author    Mori Reo <mori.reo@sabel.jp>
 */
class Test_Map_Match extends SabelTestCase
{
  private $config  = null;
  private $request = null;
  
  public static function suite()
  {
    return self::createSuite("Test_Map_Match");
  }
  
  public function setUp()
  {
    $this->config = new ConfigMap();
  }
  
  public function tearDown()
  {
    $this->config->clearRoutes();
  }
  
  public function testSimple()
  {
    $this->route("default")
         ->uri(":controller/:action")
         ->module("index");
    
    $c = $this->routing("test/test");
    $this->assertEquals("default", $c->getName());
  }
  
  public function testNoMatch()
  {
    $this->route("default")
         ->uri(":controller/:action")
         ->module("index");
    
    try {
      $c = $this->routing("test");
    } catch (Exception $e) {
      return;
    }
    
    $this->fail();
  }
  
  public function testFailMismatchUriAndElement()
  {
    $this->route("default")
         ->uri(":controller/:action")
         ->module("index");
    
    try {
      $this->routing("test/test/test");
    } catch (Exception $e) {
      return;
    }
    
    $this->fail();
  }
  
  public function testFailWithDefault()
  {
    $this->route("default")
         ->uri(":controller/:action")
         ->module("index");
    
    try {
      $this->routing("test");
    } catch (Exception $e) {
      return;
    }
    
    $this->fail();
  }
  
  public function testMatchWithDefault()
  {
    $this->route("default")
         ->uri(":controller/:action")
         ->module("index")
         ->defaults(array(":controller" => "index", ":action" => "index"));
    
    $candidate = $this->routing("test");
    $destination = $candidate->getDestination();
    
    $this->assertEquals("default", $candidate->getName());
    $this->assertEquals("index", $destination->getAction());
  }
  
  public function testMatchWithDefaultPriority()
  {
    $this->route("default")
         ->uri(":controller/:action")
         ->module("index")
         ->defaults(array(":action" => "index"));
    
    $candidate = $this->routing("test/test");
    $destination = $candidate->getDestination();
    
    $this->assertEquals("default", $candidate->getName());
    $this->assertEquals("test", $destination->getAction());
  }
  
  public function testMatchWithParameter()
  {
    $this->route("default")
         ->uri(":controller/:action/:param")
         ->module("index")
         ->requirements(array(":param" => "[0-9]+"));

    $candidate = $this->routing("test/test/1000");
    
    $this->assertEquals("default", $candidate->getName());
    $this->assertEquals("1000", $this->request->fetchParameterValue("param"));
  }
  
  public function testMismatchWithParameter()
  {
    $this->route("default")
         ->uri(":controller/:action/:param")
         ->module("index")
         ->requirements(array(":param" => "[0-9]+"));
    
    try {
      $candidate = $this->routing("test/test/test");
    } catch (Exception $e) {
      return;
    }
    
    $this->fail();
  }
  
  public function testMismatchWithMultipleParameter()
  {
    $this->route("default")
         ->uri(":controller/:action/:param/:param2")
         ->module("index")
         ->requirements(array(":param" => "[0-9]+", ":param2" => "[a-z]+"));
    
    try {
      $candidate = $this->routing("test/test/1000/1000");
    } catch (Exception $e) {
      return;
    }
    
    $this->fail();
  }
  
  public function testMatchhWithMultipleParameterWithDefaultsWithRequirements()
  {
    $this->route("default")
         ->uri(":controller/:action/:param/:param2")
         ->module("index")
         ->requirements(array(":param" => "0-9]+", ":param2" => "[a-z]+"))
         ->defaults(array(":param" => "100", ":param2" => "abc"));
    
    $candidate = $this->routing("test/test");
    $this->assertEquals("100" ,$this->request->fetchParameterValue("param"));
    $this->assertEquals("abc" ,$this->request->fetchParameterValue("param2"));
  }
  
  public function testMisMatchhWithMultipleParameterWithRequirements()
  {
    $this->route("default")
         ->uri(":controller/:action/:param/:param2")
         ->module("index")
         ->requirements(array(":param" => "[0-9]{1}", ":param2" => "[a-z]+"));
    
    try {
      $candidate = $this->routing("test/test/100/abc");
    } catch (Exception $e) {
      return;
    }
    
    $this->fail("matched");
  }
  
  public function testMultipleRoutePriority()
  {
    $this->route("article")
         ->uri(":controller/:action/:year/:month/:day")
         ->module("index")
         ->requirements(array(":year"  => "[1-3][0-9]{3}",
                              ":month" => "[0-2][0-9]",
                              ":day"   => "[0-3][0-9]"));
    
    $this->route("default")
         ->uri(":controller/:action")
         ->module("index");
    
    $candidate = $this->routing("blog/article/2008/01/20");
    $this->assertEquals("article", $candidate->getName());
    
    $candidate = $this->routing("blog/article");
    $this->assertEquals("default", $candidate->getName());
    
    try {
      $candidate = $this->routing("blog/article/9999/99/99");
    } catch (Exception $e) {
      return;
    }
    
    $this->fail();
  }
  
  public function testMultipleRoutePriorityWithDefault()
  {
    $this->route("article")
         ->uri(":controller/:action/:year/:month/:day")
         ->module("index")
         ->requirements(array(":year"  => "[1-3][0-9]{3}",
                              ":month" => "[0-2][0-9]",
                              ":day"   => "[0-3][0-9]"))
         ->defaults(array(":day" => "01"));
    
    $this->route("default")
         ->uri(":controller/:action")
         ->module("index");
    
    $candidate = $this->routing("blog/article/2008/01");
    
    $this->assertEquals("article", $candidate->getName());
    $this->assertEquals("2008", $this->request->fetchParameterValue("year"));
    $this->assertEquals("01",   $this->request->fetchParameterValue("month"));
    $this->assertEquals("01",   $this->request->fetchParameterValue("day"));
    
    $candidate = $this->routing("blog/article");
    $this->assertEquals("default", $candidate->getName());
    
    try {
      $candidate = $this->routing("blog/article/9999/99/99");
    } catch (Exception $e) {
      return;
    }
    
    $this->fail();
  }
  
  public function testMultipleRoutePriorityWithAllDefault()
  {
    $this->route("article")
         ->uri(":controller/:action/:year/:month/:day")
         ->module("index")
         ->requirements(array(":year"  => "[1-3][0-9]{3}",
                              ":month" => "[0-2][0-9]",
                              ":day"   => "[0-3][0-9]"))
         ->defaults(array(":year" => "2008", ":month" => "01", ":day" => null));
    
    $this->route("default")
         ->uri(":controller/:action")
         ->module("index")
         ->defaults(array(":action" => "test"));
    
    $candidate = $this->routing("blog/article");
    
    $this->assertEquals("article", $candidate->getName());
    $this->assertEquals("2008", $this->request->fetchParameterValue("year"));
    $this->assertEquals("01",   $this->request->fetchParameterValue("month"));
    $this->assertEquals(null,   $this->request->fetchParameterValue("day"));
    
    $candidate = $this->routing("test");
    $this->assertEquals("default", $candidate->getName());
    
    try {
      $candidate = $this->routing("blog/article/9999/99/99");
    } catch (Exception $e) {
      return;
    }
    
    $this->fail();
  }
  
  public function testWithConstant()
  {
    $this->route("admin")
         ->uri("admin/:controller/:action")
         ->module("admin");
    
    $this->route("manage")
         ->uri("manage/:controller/:action")
         ->module("manage");
    
    $candidate = $this->routing("admin/test/test");
    $this->assertEquals("admin", $candidate->getName());
    
    $candidate = $this->routing("manage/test/test");
    $this->assertEquals("manage", $candidate->getName());
  }
  
  public function testWithConstantWithDefaults()
  {
    $this->route("admin")
         ->uri("admin/:controller/:action/:param")
         ->module("admin")
         ->defaults(array(":param" => "param"));
    
    $this->route("manage")
         ->uri("manage/:controller/:action")
         ->module("manage");
    
    $candidate = $this->routing("admin/test/test");
    $destination = $candidate->getDestination();
    $this->assertEquals("admin", $candidate->getName());
    $this->assertEquals("test",  $destination->getController());
    $this->assertEquals("test",  $destination->getAction());
    $this->assertEquals("param", $this->request->fetchParameterValue("param"));
    
    $candidate = $this->routing("manage/test/test");
    $this->assertEquals("manage", $candidate->getName());
  }
  
  public function testMatchAll()
  {
    $this->route("default")
         ->uri(":controller/:action")
         ->module("admin");
    
    $this->route("matchall")
         ->uri("*")
         ->module("module")
         ->controller("controller")
         ->action("action");
    
    $candidate = $this->routing("hoge/fuga/foo/bar/baz");
    $this->assertEquals("matchall", $candidate->getName());
    
    $destination = $candidate->getDestination();
    $this->assertEquals("module",     $destination->getModule());
    $this->assertEquals("controller", $destination->getController());
    $this->assertEquals("action",     $destination->getAction());
  }
  
  protected function route($name)
  {
    return $this->config->route($name);
  }
  
  protected function request($uri)
  {
    return $request = new Sabel_Request_Object($uri);
  }
  
  protected function routing($uri)
  {
    $this->config->configure();
    
    $this->request = $request = $this->request($uri);
    if (!$candidate = $this->config->getValidCandidate($request->getUri())) {
      throw new Sabel_Exception_Runtime("map not match.");
    }
    
    $request->setParameterValues($candidate->getUriParameters());
    
    return $candidate;
  }
}

class ConfigMap extends Sabel_Map_Configurator
{
  public function configure() {}
}
