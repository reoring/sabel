<?php

/**
 * testcase for sabel.db.mysql.*
 *
 * @category  DB
 * @author    Ebine Yutaka <ebine.yutaka@sabel.jp>
 */
class Test_DB_Mysql extends Test_DB_Test
{
  public static function suite()
  {
    return self::createSuite("Test_DB_Mysql");
  }
  
  public function testConnectionRefused()
  {
    $params = array("package"  => "sabel.db.mysql",
                    "host"     => "127.0.0.1",
                    "user"     => "hogehoge",
                    "password" => "fugafuga",
                    "database" => "sdb_test");
    
    Sabel_Db_Config::add("conrefused", $params);
    $driver = new Sabel_Db_Mysql_Driver("conrefused");
    
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
    Sabel_Db_Config::add("default", Test_DB_TestConfig::getMysqlConfig());
    Test_DB_Test::$db = "MYSQL";
  }
}
