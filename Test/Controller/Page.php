<?php

/**
 * testcase of sabel.controller.Page
 *
 * @category Controller
 * @author   Ebine Yutaka <ebine.yutaka@sabel.jp>
 */
class Test_Controller_Page extends SabelTestCase
{
  public static function suite()
  {
    return self::createSuite("Test_Controller_Page");
  }
  
  public function testSetInvalidAction()
  {
    $c = $this->createController();
    try {
      $c->setAction(10000);
    } catch (Sabel_Exception_InvalidArgument $e) {
      return;
    }
    
    $this->fail();
  }
  
  public function testIndexAction()
  {
    $c = $this->createController();
    $c->initialize();
    $c->setAction("index");
    $c->execute();
    
    $this->assertTrue($c->isExecuted());
    $this->assertEquals("index", $c->getAction());
    $this->assertEquals("index", $c->getAttribute("actionResult"));
  }
  
  public function testFugaAction()
  {
    $c = $this->createController();
    $c->initialize();
    $c->execute("fuga", array("10", "20", "30"));
    
    $this->assertEquals("10 20 30", $c->getAttribute("actionResult"));
  }
  
  public function testReservedAction()
  {
    $c = $this->createController();
    $c->setAction("getRequest");
    $c->execute();
    
    $this->assertFalse($c->isExecuted());
    $this->assertEquals(Sabel_Response::NOT_FOUND, $c->getResponse()->getStatus()->getCode());
  }
  
  public function testHiddenAction()
  {
    $c = $this->createController();
    $c->setAction("hiddenAction");
    $c->execute();
    
    $this->assertFalse($c->isExecuted());
    $this->assertEquals(Sabel_Response::NOT_FOUND, $c->getResponse()->getStatus()->getCode());
  }
  
  public function testProtectedAction()
  {
    $c = $this->createController();
    $c->setAction("hoge");
    $c->execute();
    
    $this->assertFalse($c->isExecuted());
  }
  
  public function testAttributesAndResponses()
  {
    $c = $this->createController();
    $c->setAttribute("a", "10");
    $c->setAttribute("b", "20");
    $c->assign("c", "30");
    
    $this->assertEquals("10", $c->getAttribute("a"));
    $this->assertEquals("20", $c->getAttribute("b"));
    $this->assertEquals(null, $c->getAttribute("c"));
    $this->assertEquals("30", $c->getResponse()->getResponse("c"));
  }
  
  public function testAttributes()
  {
    $c = $this->createController();
    $c->setAttributes(array("a" => "10", "b" => "20"));
    $this->assertEquals("10", $c->getAttribute("a"));
    $this->assertEquals("20", $c->getAttribute("b"));
    $this->assertEquals(null, $c->getAttribute("c"));
    
    $expected = array("a" => "10", "b" => "20");
    $this->assertEquals($expected, $c->getAttributes());
  }
  
  public function testIsAttributeSet()
  {
    $c = $this->createController();
    $c->setAttribute("a", "10");
    $this->assertTrue($c->isAttributeSet("a"));
    $c->setAttribute("b", null);
    $this->assertFalse($c->isAttributeSet("b"));
  }
  
  public function testHasAttribute()
  {
    $c = $this->createController();
    $c->setAttribute("a", "10");
    $this->assertTrue($c->hasAttribute("a"));
    $c->setAttribute("b", null);
    $this->assertTrue($c->hasAttribute("b"));
  }
  
  public function testMagickMethods()
  {
    $c = $this->createController();
    $c->a = "10";
    $c->b = "20";
    $this->assertEquals("10", $c->a);
    $this->assertEquals("20", $c->b);
    $this->assertEquals(null, $c->c);
    $this->assertEquals("10", $c->getAttribute("a"));
    $this->assertEquals("20", $c->getAttribute("b"));
  }
  
  protected function createController()
  {
    $c = new TestController;
    $c->setResponse(new Sabel_Response_Object());
    
    return $c;
  }
}

class TestController extends Sabel_Controller_Page
{
  protected $hidden = array("hiddenAction");
  
  public function index()
  {
    $this->actionResult = "index";
  }
  
  public function fuga($a, $b, $c)
  {
    $this->actionResult = "$a $b $c";
  }
  
  public function hiddenAction() {}
  protected function hoge() {}
}
