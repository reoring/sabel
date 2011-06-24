<?php

class Test_DB_PdoMysql extends Test_DB_Test
{
  public static function suite()
  {
    return self::createSuite("Test_DB_PdoMysql");
  }
  
  public function testInit()
  {
    Sabel_Db_Config::add("default", Test_DB_TestConfig::getPdoMysqlConfig());
    Test_DB_Test::$db = "MYSQL";
  }
}
