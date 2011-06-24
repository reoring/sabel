<?php

/**
 * @category  Storage
 * @author    Ebine Yutaka <ebine.yutaka@sabel.jp>
 */
class Test_DB_Storage_PdoMysql extends Test_DB_Storage_Test
{
  public static function suite()
  {
    return self::createSuite("Test_DB_Storage_PdoMysql");
  }
  
  public function testInit()
  {
    Sabel_Db_Config::add("default", Test_DB_TestConfig::getPdoMysqlConfig());
    MODEL("SblKvs")->delete();
  }
}
