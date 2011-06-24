<?php

/**
 * testcase for sabel.db.mssql.*
 *
 * @category  DB
 * @author    Ebine Yutaka <ebine.yutaka@sabel.jp>
 */
class Test_DB_Mssql extends Test_DB_Test
{
  public static function suite()
  {
    return self::createSuite("Test_DB_Mssql");
  }
  
  public function testInit()
  {
    Sabel_Db_Config::add("default", Test_DB_TestConfig::getMssqlConfig());
    Test_DB_Test::$db = "MSSQL";
  }
}
