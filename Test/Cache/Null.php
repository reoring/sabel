<?php

/**
 * testcase for sabel.cache.Null
 *
 * @category  Cache
 * @author    Ebine Yutaka <ebine.yutaka@sabel.jp>
 */
class Test_Cache_Null extends SabelTestCase
{
  protected $cache = null;
  
  public static function suite()
  {
    return self::createSuite("Test_Cache_Null");
  }
  
  public function setUp()
  {
    $this->cache = Sabel_Cache_Null::create();
  }
  
  public function testRead()
  {
    $this->assertNull($this->cache->read("hoge"));
  }
  
  public function testWrite()
  {
    $this->cache->write("hoge", "value");
    $this->assertNull($this->cache->read("hoge"));
  }
  
  public function testDelete()
  {
    $this->cache->delete("hoge");
    $this->assertNull($this->cache->read("hoge"));
  }
}
