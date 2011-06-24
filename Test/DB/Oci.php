<?php

/**
 * testcase for sabel.db.oci.*
 *
 * @category  DB
 * @author    Ebine Yutaka <ebine.yutaka@sabel.jp>
 */
class Test_DB_Oci extends Test_DB_Test
{
  public static function suite()
  {
    return self::createSuite("Test_DB_Oci");
  }
  
  public function testConnectionRefused()
  {
    $params = array("package"  => "sabel.db.oci",
                    "host"     => "127.0.0.1",
                    "user"     => "hogehoge",
                    "password" => "fugafuga",
                    "database" => "XE",
                    "charset"  => "UTF-8");
    
    Sabel_Db_Config::add("conrefused", $params);
    $driver = new Sabel_Db_Oci_Driver("conrefused");
    
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
    Sabel_Db_Config::add("default", Test_DB_TestConfig::getOciConfig());
    Test_DB_Test::$db = "ORACLE";
  }
}
