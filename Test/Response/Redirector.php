<?php

/**
 * test for sabel.response.Redirector
 * using sabel.map and sabel.Context
 *
 * @category Controller
 * @author   Ebine Yutaka <ebine.yutaka@sabel.jp>
 */
class Test_Response_Redirector extends SabelTestCase
{
  public static function suite()
  {
    return self::createSuite("Test_Response_Redirector");
  }
  
  public function setUp()
  {
    $config = new TestConfigMap();
    $config->route("default")
           ->uri(":controller/:action")
           ->module("index");
    
    $this->routing($config);
  }
  
  public function testIsRedirected()
  {
    $redirector = new Sabel_Response_Redirector();
    $this->assertFalse($redirector->isRedirected());
  }
  
  public function testRedirect()
  {
    $redirector = new Sabel_Response_Redirector();
    $redirector->to("a: test");
    $this->assertTrue($redirector->isRedirected());
    $this->assertEquals("/index/test", $redirector->getUri());
  }
  
  public function testRedirectByUrl()
  {
    $redirector = new Sabel_Response_Redirector();
    $redirector->url("index/test");
    $this->assertTrue($redirector->isRedirected());
    $this->assertEquals("index/test", $redirector->getUrl());
  }
  
  public function testRedirectWithParameters()
  {
    $redirector = new Sabel_Response_Redirector();
    $redirector->to("a: test", array("page" => "1"));
    $this->assertTrue($redirector->isRedirected());
    $this->assertTrue($redirector->hasParameters());
    $this->assertEquals("/index/test?page=1", $redirector->getUri());
  }
  
  public function testUriParameter()
  {
    $redirector = new Sabel_Response_Redirector();
    $redirector->to("n: default");
    $this->assertTrue($redirector->isRedirected());
    //$this->assertEquals("index/index", $redirector->getUri());
    $this->assertEquals("/", $redirector->getUri());
  }
  
  protected function routing($config)
  {
    $request = new Sabel_Request_Object("index/index");
    
    $config->configure();
    $candidate = $config->getValidCandidate($request->getUri());
    Sabel_Context::getContext()->setCandidate($candidate);
  }
}

class TestConfigMap extends Sabel_Map_Configurator
{
  public function configure() {}
}
