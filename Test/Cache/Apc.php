<?php

/**
 * @category  Cache
 * @author    Ebine Yutaka <ebine.yutaka@sabel.jp>
 */
class Test_Cache_Apc extends Test_Cache_Test
{
  public static function suite()
  {
    if (ini_get("apc.enable_cli") === "1") {
      return self::createSuite("Test_Cache_Apc");
    } else {
      throw new Exception("must enable 'apc.enable_cli' in your php.ini");
    }
  }
  
  public function setUp()
  {
    $this->cache = Sabel_Cache_Apc::create();
    $this->cache->delete("hoge");
  }
}
