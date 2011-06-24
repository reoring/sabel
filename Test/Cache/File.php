<?php

/**
 * @category  Cache
 * @author    Ebine Yutaka <ebine.yutaka@sabel.jp>
 */
class Test_Cache_File extends Test_Cache_Test
{
  public static function suite()
  {
    $dir = SABEL_BASE . DIRECTORY_SEPARATOR . "Test" . DIRECTORY_SEPARATOR
         . "data" . DIRECTORY_SEPARATOR . "application" . DIRECTORY_SEPARATOR . "cache";
    
    define("CACHE_DIR_PATH", $dir);
    return self::createSuite("Test_Cache_File");
  }
  
  public function setUp()
  {
    $this->cache = Sabel_Cache_File::create();
    $this->cache->delete("hoge");
  }
}
