<?php

/**
 * testcase of sabel.request.Object
 *
 * @category Request
 * @author   Mori Reo <mori.reo@sabel.jp>
 * @author   Ebine Yutaka <ebine.yutaka@sabel.jp>
 */
class Test_Request_Object extends SabelTestCase
{
  public static function suite()
  {
    return self::createSuite("Test_Request_Object");
  }
  
  public function testUri()
  {
    $path = "foo/bar";
    $request = new Sabel_Request_Object("");
    $request->get($path);
    $this->assertEquals($path, $request->getUri());
  }
  
  public function testGetValue()
  {
    $request = new Sabel_Request_Object("");
    $request->get("index/index");
    $request->value("a", "1")->value("b", "2");
    $this->assertEquals("1",  $request->fetchGetValue("a"));
    $this->assertEquals("2",  $request->fetchGetValue("b"));
    $this->assertEquals(null, $request->fetchPostValue("a"));
    $this->assertEquals(null, $request->fetchPostValue("b"));
  }
  
  public function testPostValue()
  {
    $request = new Sabel_Request_Object("");
    $request->post("index/index");
    $request->value("a", "1")->value("b", "2");
    $this->assertEquals(null, $request->fetchGetValue("a"));
    $this->assertEquals(null, $request->fetchGetValue("b"));
    $this->assertEquals("1",  $request->fetchPostValue("a"));
    $this->assertEquals("2",  $request->fetchPostValue("b"));
  }
  
  public function testGetValues()
  {
    $request = new Sabel_Request_Object("");
    $request->get("index/index")->values(array("a" => "1", "b" => "2"));
    $this->assertEquals(array("a" => "1", "b" => "2"), $request->fetchGetValues());
    $this->assertEquals(array(), $request->fetchPostValues());
  }
  
  public function testPostValues()
  {
    $request = new Sabel_Request_Object("");
    $request->post("index/index")->values(array("a" => "1", "b" => "2"));
    $this->assertEquals(array(), $request->fetchGetValues());
    $this->assertEquals(array("a" => "1", "b" => "2"), $request->fetchPostValues());
  }
  
  public function testHasValueWithMethod()
  {
    $request = new Sabel_Request_Object("");
    $request->get("index/index")->values(array("a" => "1", "b" => "2"));
    $this->assertTrue($request->hasValueWithMethod("a"));
    
    $request->post("index/index");
    $this->assertFalse($request->hasValueWithMethod("a"));
  }
  
  public function testGetValueWithMethod()
  {
    $request = new Sabel_Request_Object("");
    $request->get("index/index")->values(array("a" => "1", "b" => "2"));
    $this->assertEquals("2", $request->getValueWithMethod("b"));
    
    $request->post("index/index");
    $this->assertNull($request->getValueWithMethod("b"));
  }
  
  public function testSetValue()
  {
    $request = new Sabel_Request_Object("");
    $this->assertEquals(null, $request->fetchGetValue("a"));
    $request->setGetValue("a", "1");
    $this->assertEquals("1", $request->fetchGetValue("a"));
  }
  
  public function testSetValues()
  {
    $request = new Sabel_Request_Object("");
    $this->assertEquals(null, $request->fetchGetValue("a"));
    $request->setGetValues(array("a" => "1"));
    $this->assertEquals("1", $request->fetchGetValue("a"));
  }
  
  public function testHasGetValue()
  {
    $request = new Sabel_Request_Object("");
    $this->assertFalse($request->hasGetValue("a"));
    $request->setGetValues(array("a" => "1", "b" => ""));
    $this->assertTrue($request->hasGetValue("a"));
    $this->assertFalse($request->hasGetValue("b"));
  }
  
  public function testIsGetSet()
  {
    $request = new Sabel_Request_Object("");
    $this->assertFalse($request->hasGetValue("a"));
    $request->setGetValues(array("a" => "1", "b" => ""));
    
    $this->assertTrue($request->hasGetValue("a"));
    $this->assertFalse($request->hasGetValue("b"));
    
    $this->assertTrue($request->isGetSet("a"));
    $this->assertTrue($request->isGetSet("b"));
  }
  
  public function testHasPostValue()
  {
    $request = new Sabel_Request_Object("");
    $request->post("");
    
    $this->assertFalse($request->hasPostValue("a"));
    $request->setPostValues(array("a" => "1", "b" => ""));
    $this->assertTrue($request->hasPostValue("a"));
    $this->assertFalse($request->hasPostValue("b"));
  }
  
  public function testIsPostSet()
  {
    $request = new Sabel_Request_Object("");
    $request->post("");
    
    $this->assertFalse($request->hasPostValue("a"));
    $request->setPostValues(array("a" => "1", "b" => ""));
    
    $this->assertTrue($request->hasPostValue("a"));
    $this->assertFalse($request->hasPostValue("b"));
    
    $this->assertTrue($request->isPostSet("a"));
    $this->assertTrue($request->isPostSet("b"));
  }
  
  public function testFind()
  {
    $request = new Sabel_Request_Object("");
    $request->setGetValue("a", "10");
    $request->setPostValue("b", "20");
    $request->setParameterValue("c", "30");
    
    $this->assertEquals("10", $request->find("a"));
    $this->assertEquals("20", $request->find("b"));
    $this->assertEquals("30", $request->find("c"));
  }
  
  public function testFindDuplicateValues()
  {
    $request = new Sabel_Request_Object("");
    $request->setGetValue("a", "10");
    $request->setPostValue("b", "20");
    $request->setParameterValue("b", "30");
    
    $this->assertEquals("10", $request->find("a"));
    
    try {
      $this->assertEquals("20", $request->find("b"));
    } catch (Exception $e) {
      return;
    }
    
    $this->fail();
  }
}
