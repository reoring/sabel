<?php

/**
 * testcase for sabel.db.ibase.*
 *
 * @category  DB
 * @author    Ebine Yutaka <ebine.yutaka@sabel.jp>
 */
class Test_DB_Ibase extends Test_DB_Test
{
  public static function suite()
  {
    return self::createSuite("Test_DB_Ibase");
  }
  
  public function testConnectionRefused()
  {
    $params = array("package"  => "sabel.db.ibase",
                    "host"     => "localhost",
                    "user"     => "hogehoge",
                    "password" => "fugafuga",
                    "database" => "/home/firebird/sdb_test.fdb");
    
    Sabel_Db_Config::add("conrefused", $params);
    $driver = new Sabel_Db_Ibase_Driver("conrefused");
    
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
    Sabel_Db_Config::add("default", Test_DB_TestConfig::getIbaseConfig());
    Test_DB_Test::$db = "IBASE";
  }
  
  public function testDefinedValue()
  {
    $this->assertEquals(8, IBASE_COMMITTED);
    $this->assertEquals(32, IBASE_REC_NO_VERSION);
    $this->assertEquals(40, IBASE_COMMITTED|IBASE_REC_NO_VERSION);
  }
}
