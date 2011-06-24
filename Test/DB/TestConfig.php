<?php

class Test_DB_TestConfig
{
  public static function getMysqlConfig()
  {
    return array("package"  => "sabel.db.mysql",
                 "host"     => "127.0.0.1",
                 "user"     => "root",
                 "password" => "",
                 "database" => "sdb_test");
  }
  
  public static function getMysqliConfig()
  {
    return array("package"  => "sabel.db.mysqli",
                 "host"     => "127.0.0.1",
                 "user"     => "root",
                 "password" => "",
                 "database" => "sdb_test");
  }
  
  public static function getPgsqlConfig()
  {
    return array("package"  => "sabel.db.pgsql",
                 "host"     => "127.0.0.1",
                 "user"     => "pgsql",
                 "password" => "pgsql",
                 "database" => "sdb_test");
  }
  
  public static function getIbaseConfig()
  {
    return array("package"  => "sabel.db.ibase",
                 "host"     => "localhost",
                 "user"     => "develop",
                 "password" => "develop",
                 "database" => "/home/firebird/sdb_test.fdb");
  }
  
  public static function getOciConfig()
  {
    return array("package"  => "sabel.db.oci",
                 "host"     => "127.0.0.1",
                 "user"     => "develop",
                 "password" => "develop",
                 "database" => "XE",
                 "charset"  => "UTF8");
  }
  
  public static function getMssqlConfig()
  {
    return array("package"  => "sabel.db.mssql",
                 "host"     => ".\\SQLEXPRESS",
                 "user"     => "develop",
                 "password" => "develop",
                 "database" => "sdb_test");
  }
  
  public static function getPdoMysqlConfig()
  {
    return array("package"  => "sabel.db.pdo.mysql",
                 "host"     => "127.0.0.1",
                 "user"     => "root",
                 "password" => "",
                 "database" => "sdb_test");
  }
  
  public static function getPdoPgsqlConfig()
  {
    return array("package"  => "sabel.db.pdo.pgsql",
                 "host"     => "127.0.0.1",
                 "user"     => "pgsql",
                 "password" => "pgsql",
                 "database" => "sdb_test");
  }
  
  public static function getPdoSqliteConfig()
  {
    return array("package"  => "sabel.db.pdo.sqlite",
                 "database" => SABEL_BASE . "/Test/data/sdb_test.sq3");
  }
  
  public static function getPdoOciConfig()
  {
    return array("package"  => "sabel.db.pdo.oci",
                 "host"     => "127.0.0.1",
                 "user"     => "develop",
                 "password" => "develop",
                 "database" => "XE",
                 "charset"  => "UTF8");
  }
}
