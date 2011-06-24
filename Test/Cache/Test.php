<?php

/**
 * test case for Cache/Apc, Cache/Memcache, Cache/File
 *
 * @category  Cache
 * @author    Ebine Yutaka <ebine.yutaka@sabel.jp>
 */
class Test_Cache_Test extends SabelTestCase
{
  protected $cache = null;
  
  public function testRead()
  {
    $this->assertNull($this->cache->read("hoge"));
  }
  
  public function testWrite()
  {
    $this->cache->write("hoge", "value");
    $this->assertEquals("value", $this->cache->read("hoge"));
  }
  
  public function testDelete()
  {
    $this->cache->write("hoge", "value");
    $this->assertEquals("value", $this->cache->read("hoge"));
    $this->cache->delete("hoge");
    $this->assertNull($this->cache->read("hoge"));
  }
  
  public function testArray()
  {
    $this->cache->write("hoge", array("k1" => "value", "k2" => 10, "k3" => true));
    $array = $this->cache->read("hoge");
    $this->assertTrue(is_array($array));
    $this->assertEquals("value", $array["k1"]);
    $this->assertEquals(10,      $array["k2"]);
    $this->assertEquals(true,    $array["k3"]);
  }
  
  public function testObject()
  {
    $obj = new stdClass();
    $obj->k1 = "value";
    $obj->k2 = 10;
    $obj->k3 = true;
    
    $this->cache->write("hoge", $obj);
    $object = $this->cache->read("hoge");
    $this->assertTrue(is_object($object));
    $this->assertEquals("value", $object->k1);
    $this->assertEquals(10, $object->k2);
    $this->assertEquals(true, $object->k3);
  }
}
