<?php

/**
 * testcase of sabel.ValueObject
 *
 * @category  Core
 * @author    Ebine Yutaka <ebine.yutaka@sabel.jp>
 */
class Test_ValueObject extends SabelTestCase
{
  public static function suite()
  {
    return self::createSuite("Test_ValueObject");
  }
  
  /**
   * @test
   */
  public function simpleTest()
  {
    $object = new Sabel_ValueObject();
    $object->set("a", 10);
    $object->set("b", "20");
    $object->set("c", true);
    $object->set("d", new stdClass());
    $object->set("e", array("foo", "bar"));
    
    $this->assertEquals(10, $object->get("a"));
    $this->assertEquals("20", $object->get("b"));
    $this->assertEquals(true, $object->get("c"));
    $this->assertEquals(new stdClass(), $object->get("d"));
    $this->assertEquals(array("foo", "bar"), $object->get("e"));
  }
  
  /**
   * @test
   */
  public function simpleTest2()
  {
    $object = new Sabel_ValueObject();
    $object->a = 10;
    $object->b = "20";
    $object->c = true;
    $object->d = new stdClass();
    $object->e = array("foo", "bar");
    
    $this->assertEquals(10, $object->a);
    $this->assertEquals("20", $object->b);
    $this->assertEquals(true, $object->c);
    $this->assertEquals(new stdClass(), $object->d);
    $this->assertEquals(array("foo", "bar"), $object->e);
  }
  
  /**
   * @test
   */
  public function toArray()
  {
    $object = new Sabel_ValueObject();
    $object->a = 10;
    $object->b = "20";
    $object->c = true;
    
    $array = $object->toArray();
    
    $this->assertEquals(10, $array["a"]);
    $this->assertEquals("20", $array["b"]);
    $this->assertEquals(true, $array["c"]);
  }
  
  /**
   * @test
   */
  public function fromArray()
  {
    $object = Sabel_ValueObject::fromArray(array(
      "a" => 10,
      "b" => "20",
      "c" => true
    ));
    
    $this->assertEquals(10, $object->a);
    $this->assertEquals("20", $object->b);
    $this->assertEquals(true, $object->c);
  }
  
  /**
   * @test
   */
  public function containts()
  {
    $object = new Sabel_ValueObject();
    $object->a = 10;
    $object->b = null;
    
    $this->assertTrue($object->has("a"));
    $this->assertFalse($object->has("b"));
    $this->assertTrue($object->exists("a"));
    $this->assertTrue($object->exists("b"));
  }
  
  /**
   * @test
   */
  public function remove()
  {
    $object = new Sabel_ValueObject();
    $object->a = 10;
    
    $this->assertTrue($object->has("a"));
    
    $object->remove("a");
    
    $this->assertFalse($object->has("a"));
  }
  
  /**
   * @test
   */
  public function merge()
  {
    $object = new Sabel_ValueObject();
    $object->a = 10;
    $object->b = 20;
    
    $this->assertEquals(2, count($object->toArray()));
    
    $object->merge(array("c" => 30, "d" => 40));
    
    $this->assertEquals(4, count($object->toArray()));
    
    $object2 = new Sabel_ValueObject();
    $object2->e = 50;
    $object2->f = 60;
    
    $object->merge($object2);
    
    $this->assertEquals(6, count($object->toArray()));
    
    $this->assertEquals(10, $object->a);
    $this->assertEquals(20, $object->b);
    $this->assertEquals(30, $object->c);
    $this->assertEquals(40, $object->d);
    $this->assertEquals(50, $object->e);
    $this->assertEquals(60, $object->f);
  }
}
