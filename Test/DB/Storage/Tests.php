<?php

require_once ("Test/DB/Storage/Test.php");
require_once ("Test/DB/Storage/Mysql.php");
require_once ("Test/DB/Storage/Mysqli.php");
require_once ("Test/DB/Storage/Pgsql.php");
require_once ("Test/DB/Storage/Oci.php");
require_once ("Test/DB/Storage/Ibase.php");
require_once ("Test/DB/Storage/PdoSqlite.php");
require_once ("Test/DB/Storage/PdoMysql.php");
require_once ("Test/DB/Storage/PdoPgsql.php");
require_once ("Test/DB/Storage/PdoOci.php");

/**
 * load tests for Statement
 *
 * @category  DB
 * @author    Ebine Yutaka <ebine.yutaka@sabel.jp>
 */
class Test_DB_Storage_Tests
{
  public static function main()
  {
    PHPUnit_TextUI_TestRunner::run(self::suite());
  }
  
  public static function suite()
  {
    $suite = new PHPUnit_Framework_TestSuite();
    
    if (extension_loaded("mysql")) {
      $suite->addTest(Test_DB_Storage_Mysql::suite());
    }
    
    if (extension_loaded("mysqli")) {
      $suite->addTest(Test_DB_Storage_Mysqli::suite());
    }
    
    if (extension_loaded("pgsql")) {
      $suite->addTest(Test_DB_Storage_Pgsql::suite());
    }
    
    if (extension_loaded("oci8")) {
      $suite->addTest(Test_DB_Storage_Oci::suite());
    }
    
    if (extension_loaded("interbase")) {
      $suite->addTest(Test_DB_Storage_Ibase::suite());
    }
    
    if (extension_loaded("pdo_sqlite")) {
      $suite->addTest(Test_DB_Storage_PdoSqlite::suite());
    }
    
    if (extension_loaded("pdo_mysql")) {
      $suite->addTest(Test_DB_Storage_PdoMysql::suite());
    }
    
    if (extension_loaded("pdo_pgsql")) {
      $suite->addTest(Test_DB_Storage_PdoPgsql::suite());
    }
    
    if (extension_loaded("pdo_oci")) {
      $suite->addTest(Test_DB_Storage_PdoOci::suite());
    }
    
    return $suite;
  }
}
