<?php

/**
 * @category  Cache
 * @author    Ebine Yutaka <ebine.yutaka@sabel.jp>
 */
class Test_Cache_Memcache extends Test_Cache_Test
{
  public static function suite()
  {
    return self::createSuite("Test_Cache_Memcache");
  }
  
  public function setUp()
  {
    $this->cache = Sabel_Cache_Memcache::create();
    $this->cache->delete("hoge");
  }
}
