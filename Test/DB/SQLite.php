<?php

class Test_DB_SQLite extends Test_DB_Test
{
  public static function suite()
  {
    return self::createSuite("Test_DB_SQLite");
  }
  
  public function testInit()
  {
    Sabel_Db_Config::add("default", Test_DB_TestConfig::getPdoSqliteConfig());
    Test_DB_Test::$db = "SQLITE";
  }
}
