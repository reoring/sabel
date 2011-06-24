<?php

/**
 * testcase for sabel.db.pgsql.*
 *
 * @category  DB
 * @author    Ebine Yutaka <ebine.yutaka@sabel.jp>
 */
class Test_DB_Pgsql extends Test_DB_Test
{
  public static function suite()
  {
    return self::createSuite("Test_DB_Pgsql");
  }
  
  public function testConnectionRefused()
  {
    return;
    $params = array("package"  => "sabel.db.pgsql",
                    "host"     => "localhost",
                    "user"     => "hogehoge",
                    "password" => "fugafuga",
                    "database" => "sdb_test");
    
    Sabel_Db_Config::add("conrefused", $params);
    $driver = new Sabel_Db_Pgsql_Driver("conrefused");
    
    try {
      $c = error_reporting(0);
      $resource = Sabel_Db_Connection::connect($driver);
      error_reporting($c);
    } catch (Sabel_Db_Exception_Connection $e) {
      return;
    }
    
    $this->fail();
  }
  
  public function testInit()
  {
    Sabel_Db_Config::add("default", Test_DB_TestConfig::getPgsqlConfig());
    Test_DB_Test::$db = "PGSQL";
  }
}
