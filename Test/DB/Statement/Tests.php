<?php

require_once ("Test/DB/Statement/Mysql.php");
require_once ("Test/DB/Statement/Pgsql.php");
require_once ("Test/DB/Statement/Oci.php");
require_once ("Test/DB/Statement/Ibase.php");

/**
 * load tests for Statement
 *
 * @category  DB
 * @author    Ebine Yutaka <ebine.yutaka@sabel.jp>
 */
class Test_DB_Statement_Tests
{
  public static function main()
  {
    PHPUnit_TextUI_TestRunner::run(self::suite());
  }
  
  public static function suite()
  {
    $suite = new PHPUnit_Framework_TestSuite();
    
    if (extension_loaded("mysql")) {
      $suite->addTest(Test_DB_Statement_Mysql::suite());
    }
    
    if (extension_loaded("pgsql")) {
      $suite->addTest(Test_DB_Statement_Pgsql::suite());
    }
    
    if (extension_loaded("oci8")) {
      $suite->addTest(Test_DB_Statement_Oci::suite());
    }
    
    if (extension_loaded("interbase")) {
      $suite->addTest(Test_DB_Statement_Ibase::suite());
    }
    
    return $suite;
  }
}
