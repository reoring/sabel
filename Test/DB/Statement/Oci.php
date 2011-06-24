<?php

/**
 * testcase of sabel.db.oci.Statement
 *
 * @category  DB
 * @author    Ebine Yutaka <ebine.yutaka@sabel.jp>
 */
class Test_DB_Statement_Oci extends SabelTestCase
{
  public static function suite()
  {
    return self::createSuite("Test_DB_Statement_Oci");
  }
  
  public function testInit()
  {
    Sabel_Db_Config::add("default", Test_DB_TestConfig::getOciConfig());
  }
  
  public function testQuoteIdentifier()
  {
    $stmt = Sabel_Db::createStatement("default");
    $this->assertEquals('"FOO"', $stmt->quoteIdentifier("foo"));
    $this->assertEquals('"BAR"', $stmt->quoteIdentifier("bar"));
  }
  
  public function testBuildSelectQuery()
  {
    $stmt = Sabel_Db::createStatement("default");
    $stmt->type(Sabel_Db_Statement::SELECT);
    $stmt->setMetadata(Sabel_Db_Metadata::getTableInfo("student"));
    $expected = 'SELECT "ID", "NAME" FROM "STUDENT"';
    $this->assertEquals($expected, $stmt->getQuery());
  }
  
  public function testBuildSelectWhereQuery()
  {
    $stmt = Sabel_Db::createStatement("default");
    $stmt->type(Sabel_Db_Statement::SELECT);
    $stmt->setMetadata(Sabel_Db_Metadata::getTableInfo("student"));
    $stmt->where('WHERE "ID" = 1');
    $expected = 'SELECT "ID", "NAME" FROM "STUDENT" WHERE "ID" = 1';
    $this->assertEquals($expected, $stmt->getQuery());
  }
  
  public function testBuildSelectOrderByQuery()
  {
    $stmt = Sabel_Db::createStatement("default");
    $stmt->type(Sabel_Db_Statement::SELECT);
    $stmt->setMetadata(Sabel_Db_Metadata::getTableInfo("student"));
    $stmt->constraints(array("order" => array("id" => "DESC")));
    $expected = 'SELECT "ID", "NAME" FROM "STUDENT" ORDER BY "ID" DESC';
    $this->assertEquals($expected, $stmt->getQuery());
  }
  
  public function testBuildSelectOrderByQuery2()
  {
    $stmt = Sabel_Db::createStatement("default");
    $stmt->type(Sabel_Db_Statement::SELECT);
    $stmt->setMetadata(Sabel_Db_Metadata::getTableInfo("student"));
    $stmt->constraints(array("order" => array("id" => "DESC", "name" => "ASC")));
    $expected = 'SELECT "ID", "NAME" FROM "STUDENT" ORDER BY "ID" DESC, "NAME" ASC';
    $this->assertEquals($expected, $stmt->getQuery());
  }
  
  public function testClose()
  {
    Sabel_Db_Metadata::clear();
    Sabel_Db_Connection::closeAll();
  }
}
