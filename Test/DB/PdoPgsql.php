<?php

class Test_DB_PdoPgsql extends Test_DB_Test
{
  public static function suite()
  {
    return self::createSuite("Test_DB_PdoPgsql");
  }
  
  public function testInit()
  {
    Sabel_Db_Config::add("default", Test_DB_TestConfig::getPdoPgsqlConfig());
    Test_DB_Test::$db = "PGSQL";
  }
}
