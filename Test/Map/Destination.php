<?php

/**
 * @category  Map
 * @author    Ebine Yutaka <ebine.yutaka@sabel.jp>
 */
class Test_Map_Destination extends SabelTestCase
{
  public static function suite()
  {
    return self::createSuite("Test_Map_Destination");
  }
  
  public function testDestination()
  {
    $destination = $this->createDestination();
    $this->assertEquals("module", $destination->getModule());
    $this->assertEquals("controller", $destination->getController());
    $this->assertEquals("action", $destination->getAction());
  }
  
  public function testModule()
  {
    $destination = $this->createDestination();
    $this->assertTrue($destination->hasModule());
    $destination->setModule("admin");
    $this->assertEquals("admin", $destination->getModule());
  }
  
  public function testController()
  {
    $destination = $this->createDestination();
    $this->assertTrue($destination->hasController());
    $destination->setController("main");
    $this->assertEquals("main", $destination->getController());
  }
  
  public function testAction()
  {
    $destination = $this->createDestination();
    $this->assertTrue($destination->hasAction());
    $destination->setAction("index");
    $this->assertEquals("index", $destination->getAction());
  }
  
  public function testToArray()
  {
    list ($m, $c, $a) = $this->createDestination()->toArray();
    $this->assertEquals("module", $m);
    $this->assertEquals("controller", $c);
    $this->assertEquals("action", $a);
  }
  
  public function testInvalidModuleSet()
  {
    try {
      $this->createDestination()->setModule(1);
    } catch (Exception $e) {
      return;
    }
    
    $this->fail();
  }
  
  public function testInvalidControllerSet()
  {
    try {
      $this->createDestination()->setController(false);
    } catch (Exception $e) {
      return;
    }
    
    $this->fail();
  }
  
  public function testInvalidActionSet()
  {
    try {
      $this->createDestination()->setAction(new stdClass());
    } catch (Exception $e) {
      return;
    }
    
    $this->fail();
  }
  
  private function createDestination()
  {
    $param = array("module" => "module", "controller" => "controller", "action" => "action");
    return new Sabel_Map_Destination($param);
  }
}
