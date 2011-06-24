<?php

/**
 * TestCase for sabel.cookie.InMemory
 *
 * @category  Cookie
 * @author    Ebine Yutaka <ebine.yutaka@sabel.jp>
 */
class Test_Cookie_InMemory extends SabelTestCase
{
  private $cookie = null;
  
  public static function suite()
  {
    return self::createSuite("Test_Cookie_InMemory");
  }
  
  public function setUp()
  {
    $this->cookie = Sabel_Cookie_InMemory::create();
  }
  
  public function testSimple()
  {
    $this->cookie->set("foo", "10");
    $this->assertEquals("10", $this->cookie->get("foo"));
    $this->assertEquals(null, $this->cookie->get("bar"));
  }
  
  public function testPath()
  {
    $this->setUri("/");
    $this->cookie->set("bar", "20", array("path" => "/bar"));
    $this->assertEquals(null, $this->cookie->get("bar"));
    
    # request: http://localhost/foo
    $this->setUri("/foo");
    $this->assertEquals(null, $this->cookie->get("bar"));
    
    # request: http://localhost/bar
    $this->setUri("/bar");
    $this->assertEquals("20", $this->cookie->get("bar"));
    
    # request: http://localhost/bar/baz
    $this->setUri("/bar/baz");
    $this->assertEquals("20", $this->cookie->get("bar"));
  }
  
  public function testExpire()
  {
    $this->setUri("/");
    $this->cookie->set("hoge", "30");
    $this->assertEquals("30", $this->cookie->get("hoge"));
    
    $this->cookie->set("hoge", "30", array("expire" => time() - 3600));
    $this->assertEquals(null, $this->cookie->get("hoge"));
  }
  
  public function testDelete()
  {
    $this->setUri("/");
    $this->assertNotNull($this->cookie->get("foo"));
    
    $this->cookie->delete("foo");
    $this->assertNull($this->cookie->get("foo"));
  }
  
  protected function setUri($uri)
  {
    $bus = Sabel_Context::getContext()->getBus();
    if (is_object($bus) && ($request = $bus->get("request"))) {
      $request->setUri($uri);
    }
    
    $_SERVER["REQUEST_URI"] = $uri;
  }
}
