<?php

/**
 * testcase for sabel.Object
 *
 * @category  Core
 * @author    Ebine Yutaka <ebine.yutaka@sabel.jp>
 */
class Test_Object extends SabelTestCase
{
  public static function suite()
  {
    return self::createSuite("Test_Object");
  }
  
  public function testHasMethod()
  {
    $obj = new TestObject();
    $this->assertTrue($obj->hasMethod("hoge"));
    $this->assertFalse($obj->hasMethod("fuga"));
  }
  
  public function testGetName()
  {
    $obj = new TestObject();
    $this->assertEquals("TestObject", $obj->getName());
    $this->assertNotEquals("_TestObject", $obj->getName());
  }
  
  public function testHashCode()
  {
    $obj = new TestObject();
    $hc  = "ca3a664fbcf20ac679384e974cf3d052ffd64695";
    $this->assertEquals($hc, $obj->hashCode());
    $this->assertEquals($hc, $obj->__toString());
  }
  
  public function testEquals()
  {
    $obj1 = new TestObject();
    $obj2 = new TestObject();
    
    $this->assertTrue($obj1->equals($obj2));
    $obj1->attr = 20;
    $this->assertFalse($obj1->equals($obj2));
  }
  
  public function testReflection()
  {
    $obj = new TestObject();
    $reflection = $obj->getReflection();
    $this->assertTrue($reflection instanceof ReflectionClass);
    $this->assertTrue($reflection instanceof Sabel_Reflection_Class);
    $this->assertEquals("TestObject", $reflection->getName());
    $this->assertTrue($reflection->hasMethod("hoge"));
  }
}

class TestObject extends Sabel_Object
{
  public $attr = 10;
  public function hoge() {}
}
