<?php

/**
 * testcase for sabel.db.Config
 *
 * @category  DB
 * @author    Ebine Yutaka <ebine.yutaka@sabel.jp>
 */
class Test_DB_Config extends SabelTestCase
{
  public static function suite()
  {
    return self::createSuite("Test_DB_Config");
  }
  
  public function testInitialize()
  {
    Sabel_Db_Config::initialize(new TestDatabaseConfig());
    $config = Sabel_Db_Config::get("configtest");
    $this->assertEquals("localhost", $config["host"]);
    $this->assertEquals("mydb", $config["database"]);
  }
  
  public function testDefaultSchemaName()
  {
    $params = array("package"  => "sabel.db.mysql",
                    "database" => "mydb");
    
    Sabel_Db_Config::add("configtest", $params);
    $this->assertEquals("mydb", Sabel_Db_Config::getSchemaName("configtest"));
    
    $params = array("package"  => "sabel.db.pgsql",
                    "database" => "mydb");
    
    Sabel_Db_Config::add("configtest", $params);
    $this->assertEquals("public", Sabel_Db_Config::getSchemaName("configtest"));
    
    $params = array("package"  => "sabel.db.pdo.pgsql",
                    "database" => "mydb");
    
    Sabel_Db_Config::add("configtest", $params);
    $this->assertEquals("public", Sabel_Db_Config::getSchemaName("configtest"));
    
    $params = array("package"  => "sabel.db.oci",
                    "database" => "mydb", "user" => "webuser");
    
    Sabel_Db_Config::add("configtest", $params);
    $this->assertEquals("WEBUSER", Sabel_Db_Config::getSchemaName("configtest"));
    
    $params = array("package"  => "sabel.db.pdo.oci",
                    "database" => "mydb", "user" => "webuser");
    
    Sabel_Db_Config::add("configtest", $params);
    $this->assertEquals("WEBUSER", Sabel_Db_Config::getSchemaName("configtest"));
  }
  
  public function testSchemaNameSet()
  {
    $params = array("package"  => "sabel.db.mysql",
                    "database" => "mydb", "schema" => "hoge");
    
    Sabel_Db_Config::add("configtest", $params);
    $this->assertEquals("hoge", Sabel_Db_Config::getSchemaName("configtest"));
    
    $params = array("package"  => "sabel.db.pgsql",
                    "database" => "mydb", "schema" => "hoge");
    
    Sabel_Db_Config::add("configtest", $params);
    $this->assertEquals("hoge", Sabel_Db_Config::getSchemaName("configtest"));
    
    $params = array("package"  => "sabel.db.oci",
                    "database" => "mydb", "schema" => "HOGE");
    
    Sabel_Db_Config::add("configtest", $params);
    $this->assertEquals("HOGE", Sabel_Db_Config::getSchemaName("configtest"));
  }
  
  public function testSchemaNameOfCustomPackage()
  {
    $params = array("package"  => "my.db.org",
                    "database" => "mydb", "schema" => "hoge");
    
    Sabel_Db_Config::add("configtest", $params);
    $this->assertEquals("hoge", Sabel_Db_Config::getSchemaName("configtest"));
    
    $params = array("package"  => "my.db.org",
                    "database" => "mydb");
    
    Sabel_Db_Config::add("configtest", $params);
    
    try {
      Sabel_Db_Config::getSchemaName("configtest");
    } catch (Sabel_Db_Exception $e) {
      return;
    }
    
    $this->fail();
  }
}

class TestDatabaseConfig implements Sabel_Config
{
  public function configure()
  {
    $params = array("configtest" => array(
                      "package"  => "sabel.db.mysql",
                      "host"     => "localhost",
                      "database" => "mydb",
                      "user"     => "root",
                      "password" => "")
                   );
    
    return $params;
  }
}
