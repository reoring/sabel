<?php

/**
 * Sabel_Db_Mysql_Metadata
 *
 * @category   DB
 * @package    org.sabel.db
 * @author     Ebine Yutaka <ebine.yutaka@sabel.jp>
 * @copyright  2004-2008 Mori Reo <mori.reo@sabel.jp>
 * @license    http://www.opensource.org/licenses/bsd-license.php  BSD License
 */
class Sabel_Db_Mysql_Metadata extends Sabel_Db_Abstract_Metadata
{
  public function getTable($tblName)
  {
    $schema = parent::getTable($tblName);
    $schema->setTableEngine($this->getTableEngine($tblName));
    
    return $schema;
  }
  
  public function getTableList()
  {
    $sql = "SELECT table_name FROM information_schema.tables "
         . "WHERE table_schema = '{$this->schemaName}'";
    
    $rows = $this->driver->execute($sql);
    if (empty($rows)) return array();
    
    $tables = array();
    foreach ($rows as $row) {
      $row = array_change_key_case($row);
      $tables[] = $row["table_name"];
    }
    
    return $tables;
  }
  
  protected function createColumns($tblName)
  {
    $sql = <<<SQL
SELECT
  column_name, data_type, is_nullable, column_default,
  column_comment, column_key, column_type, character_maximum_length, extra
  FROM information_schema.columns
  WHERE table_schema = '{$this->schemaName}' AND table_name = '{$tblName}'
SQL;
    
    $rows = $this->driver->execute($sql);
    if (empty($rows)) return array();
    
    $columns = array();
    foreach ($rows as $row) {
      $colName = $row["column_name"];
      $columns[$colName] = $this->createColumn($row);
    }
    
    return $columns;
  }
  
  protected function createColumn($row)
  {
    $column = new Sabel_Db_Metadata_Column();
    $column->name = $row["column_name"];
    $column->nullable = ($row["is_nullable"] !== "NO");
    
    if ($row["column_type"] === "tinyint(1)") {
      $column->type = Sabel_Db_Type::BOOL;
    } else {
      Sabel_Db_Type_Manager::create()->applyType($column, $row["data_type"]);
    }
    
    $this->setDefault($column, $row["column_default"]);
    $column->primary   = ($row["column_key"] === "PRI");
    $column->increment = ($row["extra"] === "auto_increment");
    
    if ($column->primary) {
      $column->nullable = false;
    }
    
    if ($column->isString()) {
      $column->max = (int)$row["character_maximum_length"];
    }
    
    return $column;
  }
  
  public function getForeignKeys($tblName)
  {
    $exp = explode(".", $this->getMysqlVersion());
    
    if (isset($exp[1]) && $exp[1] === "0") {
      return $this->getForeignKeys50($tblName);
    } else {
      return $this->getForeignKeys51($tblName);
    }
  }
  
  public function getUniques($tblName)
  {
    $sql = <<<SQL
SELECT
  tc.constraint_name AS unique_key, kcu.column_name
  FROM information_schema.table_constraints tc
    INNER JOIN information_schema.key_column_usage kcu
      ON tc.constraint_name = kcu.constraint_name
  WHERE tc.constraint_schema = kcu.constraint_schema
    AND tc.table_name = '{$tblName}'
    AND kcu.table_name = '{$tblName}'
    AND tc.constraint_schema = '{$this->schemaName}'
    AND tc.constraint_type = 'UNIQUE'
SQL;
    
    $rows = $this->driver->execute($sql);
    if (empty($rows)) return null;
    
    $uniques = array();
    foreach ($rows as $row) {
      $key = $row["unique_key"];
      $uniques[$key][] = $row["column_name"];
    }
    
    return array_values($uniques);
  }
  
  public function getTableEngine($tblName)
  {
    $rows = $this->driver->execute("SHOW TABLE STATUS WHERE Name='{$tblName}'");
    return $rows[0]["Engine"];
  }
  
  protected function getMysqlVersion()
  {
    $rows = $this->driver->execute("SELECT VERSION() AS version");
    return $rows[0]["version"];
  }
  
  private function getForeignKeys50($tblName)
  {
    $schemaName = $this->schemaName;
    $result = $this->driver->execute("SHOW CREATE TABLE `{$tblName}`");
    if (!isset($result[0]["Create Table"])) return null;
    
    $createSql = $result[0]["Create Table"];
    preg_match_all("/CONSTRAINT .+ FOREIGN KEY (.+)/", $createSql, $matches);
    if (empty($matches[1])) return null;
    
    $columns = array();
    foreach ($matches[1] as $match) {
      $tmp = explode(")", str_replace(" REFERENCES ", "", $match));
      $column = substr($tmp[0], 2, -1);
      
      $tmp2 = array_map("trim", explode("(", $tmp[1]));
      $columns[$column]["referenced_table"]  = substr($tmp2[0], 1, -1);
      $columns[$column]["referenced_column"] = substr($tmp2[1], 1, -1);
      
      $rule = trim($tmp[2]);
      $columns[$column]["on_delete"] = $this->getRule($rule, "ON DELETE");
      $columns[$column]["on_update"] = $this->getRule($rule, "ON UPDATE");
    }
    
    return $columns;
  }
  
  private function getRule($rule, $ruleName)
  {
    if (($pos = strpos($rule, $ruleName)) !== false) {
      $on = substr($rule, $pos + 10);
      if (($pos = strpos($on, " ON")) !== false) {
        $on = substr($on, 0, $pos);
      }
      
      return trim(str_replace(",", "", $on));
    } else {
      return "NO ACTION";
    }
  }
  
  private function getForeignKeys51($tblName)
  {
    $sql = <<<SQL
SELECT
  kcu.column_name, kcu.referenced_table_name AS ref_table,
  kcu.referenced_column_name ref_column, rc.delete_rule, rc.update_rule
  FROM information_schema.table_constraints AS tc
    INNER JOIN information_schema.referential_constraints AS rc
      ON rc.constraint_name = tc.constraint_name
    INNER JOIN information_schema.key_column_usage AS kcu
      ON kcu.constraint_name = tc.constraint_name
  WHERE tc.table_schema = '{$this->schemaName}'
    AND tc.table_name = '{$tblName}'
    AND tc.constraint_type = 'FOREIGN KEY'
    AND rc.constraint_schema = '{$this->schemaName}'
    AND rc.table_name = '{$tblName}'
    AND kcu.constraint_schema = '{$this->schemaName}'
    AND kcu.table_name = '{$tblName}';
SQL;
    
    $rows = $this->driver->execute($sql);
    if (empty($rows)) return null;
    
    $columns = array();
    foreach ($rows as $row) {
      $column = $row["column_name"];
      $columns[$column]["referenced_table"]  = $row["ref_table"];
      $columns[$column]["referenced_column"] = $row["ref_column"];
      $columns[$column]["on_delete"]         = $row["delete_rule"];
      $columns[$column]["on_update"]         = $row["update_rule"];
    }
    
    return $columns;
  }
  
  private function setDefault($co, $default)
  {
    if ($default === null) {
      $co->default = null;
    } else {
      $this->setDefaultValue($co, $default);
    }
  }
}
