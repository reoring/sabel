<?php

/**
 * testcase of sabel.db.mysql.Statement
 *
 * @category  DB
 * @author    Ebine Yutaka <ebine.yutaka@sabel.jp>
 */
class Test_DB_Statement_Mysql extends SabelTestCase
{
  public static function suite()
  {
    return self::createSuite("Test_DB_Statement_Mysql");
  }
  
  public function testInit()
  {
    Sabel_Db_Config::add("default", Test_DB_TestConfig::getMysqlConfig());
  }
  
  public function testQuoteIdentifier()
  {
    $stmt = Sabel_Db::createStatement("default");
    $this->assertEquals("`foo`", $stmt->quoteIdentifier("foo"));
    $this->assertEquals("`bar`", $stmt->quoteIdentifier("bar"));
  }
  
  public function testBuildSelectQuery()
  {
    $stmt = Sabel_Db::createStatement("default");
    $stmt->type(Sabel_Db_Statement::SELECT);
    $stmt->setMetadata(Sabel_Db_Metadata::getTableInfo("student"));
    $expected = "SELECT `id`, `name` FROM `student`";
    $this->assertEquals($expected, $stmt->getQuery());
  }
  
  public function testBuildSelectWhereQuery()
  {
    $stmt = Sabel_Db::createStatement("default");
    $stmt->type(Sabel_Db_Statement::SELECT);
    $stmt->setMetadata(Sabel_Db_Metadata::getTableInfo("student"));
    $stmt->where("WHERE `id` = 1");
    $expected = "SELECT `id`, `name` FROM `student` WHERE `id` = 1";
    $this->assertEquals($expected, $stmt->getQuery());
  }
  
  public function testBuildSelectOrderByQuery()
  {
    $stmt = Sabel_Db::createStatement("default");
    $stmt->type(Sabel_Db_Statement::SELECT);
    $stmt->setMetadata(Sabel_Db_Metadata::getTableInfo("student"));
    $stmt->constraints(array("order" => array("id" => array("mode" => "DESC", "nulls" => "LAST"))));
    $expected = "SELECT `id`, `name` FROM `student` ORDER BY `id` IS NULL, `id` DESC";
    $this->assertEquals($expected, $stmt->getQuery());
  }
  
  public function testBuildSelectOrderByQuery2()
  {
    $stmt = Sabel_Db::createStatement("default");
    $stmt->type(Sabel_Db_Statement::SELECT);
    $stmt->setMetadata(Sabel_Db_Metadata::getTableInfo("student"));
    $stmt->constraints(array("order" => array("id" => array("mode" => "DESC", "nulls" => "LAST"), "name" => array("mode" => "ASC", "nulls" => "LAST"))));
    $expected = "SELECT `id`, `name` FROM `student` ORDER BY `id` IS NULL, `id` DESC, `name` IS NULL, `name` ASC";
    $this->assertEquals($expected, $stmt->getQuery());
  }
  
  public function testEscapeString()
  {
    $stmt = Sabel_Db::createStatement("default");
    $this->assertEquals(array("'a\'b\\\\z'"), $stmt->escape(array("a'b\z")));
  }
  
  public function testClose()
  {
    Sabel_Db_Metadata::clear();
    Sabel_Db_Connection::closeAll();
  }
}
