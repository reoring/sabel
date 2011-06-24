<?php

define("MODELS_DIR_PATH", "/");

require_once ("Test/DB/Test.php");
require_once ("Test/DB/Mysql.php");
require_once ("Test/DB/Mssql.php");
require_once ("Test/DB/PdoMysql.php");
require_once ("Test/DB/Mysqli.php");
require_once ("Test/DB/Pgsql.php");
require_once ("Test/DB/PdoPgsql.php");
require_once ("Test/DB/SQLite.php");
require_once ("Test/DB/Ibase.php");
require_once ("Test/DB/Oci.php");
require_once ("Test/DB/PdoOci.php");
require_once ("Test/DB/SchemaColumn.php");
require_once ("Test/DB/Config.php");

class Condition extends Sabel_Db_Condition{}

define("EQUAL",         Condition::EQUAL);
define("ISNULL",        Condition::ISNULL);
define("ISNOTNULL",     Condition::ISNOTNULL);
define("IN",            Condition::IN);
define("LIKE",          Condition::LIKE);
define("BETWEEN",       Condition::BETWEEN);
define("GREATER_EQUAL", Condition::GREATER_EQUAL);
define("GREATER_THAN",  Condition::GREATER_THAN);
define("LESS_EQUAL",    Condition::LESS_EQUAL);
define("LESS_THAN",     Condition::LESS_THAN);
define("DIRECT",        Condition::DIRECT);

define("LIKE_BEGINS_WITH", Sabel_Db_Condition_Like::BEGINS_WITH);
define("LIKE_ENDS_WITH",   Sabel_Db_Condition_Like::ENDS_WITH);
define("LIKE_CONTAINS",    Sabel_Db_Condition_Like::CONTAINS);

/**
 * load tests of sabel.db.*
 *
 * @category  DB
 * @author    Ebine Yutaka <ebine.yutaka@sabel.jp>
 */
class Test_DB_Tests
{
  public static function main()
  {
    PHPUnit_TextUI_TestRunner::run(self::suite());
  }
  
  public static function suite()
  {
    $suite = new PHPUnit_Framework_TestSuite();
    
    if (extension_loaded("mysql")) {
      $suite->addTest(Test_DB_Mysql::suite());
    }
    
    if (extension_loaded("mysqli")) {
      $suite->addTest(Test_DB_Mysqli::suite());
    }
    
    if (extension_loaded("pgsql")) {
      $suite->addTest(Test_DB_Pgsql::suite());
    }
    
    if (extension_loaded("pdo_mysql")) {
      $suite->addTest(Test_DB_PdoMysql::suite());
    }
    
    if (extension_loaded("pdo_pgsql")) {
      $suite->addTest(Test_DB_PdoPgsql::suite());
    }
    
    if (extension_loaded("interbase")) {
      $suite->addTest(Test_DB_Ibase::suite());
    }
    
    //if (extension_loaded("mssql")) {
    //  $suite->addTest(Test_DB_Mssql::suite());
    //}
    
    if (extension_loaded("oci8")) {
      $suite->addTest(Test_DB_Oci::suite());
    }
    
    if (extension_loaded("pdo_sqlite")) {
      $suite->addTest(Test_DB_SQLite::suite());
    }
    
    if (extension_loaded("pdo_oci")) {
      $suite->addTest(Test_DB_PdoOci::suite());
    }
    
    $suite->addTest(Test_DB_SchemaColumn::suite());
    $suite->addTest(Test_DB_Config::suite());
    
    return $suite;
  }
}
