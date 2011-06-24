<?php

/**
 * Sabel_Db_Mssql_Metadata
 *
 * @category   DB
 * @package    org.sabel.db
 * @author     Ebine Yutaka <ebine.yutaka@sabel.jp>
 * @copyright  2004-2008 Mori Reo <mori.reo@sabel.jp>
 * @license    http://www.opensource.org/licenses/bsd-license.php  BSD License
 */
class Sabel_Db_Mssql_Metadata extends Sabel_Db_Abstract_Metadata
{
  /**
   * @var array
   */
  private $sequences = array();
  
  /**
   * @var array
   */
  private $primaryKeys = array();
  
  public function getTableList()
  {
    $sql = "SELECT table_name FROM INFORMATION_SCHEMA.TABLES "
         . "WHERE table_schema = '{$this->schemaName}'";
    
    $rows = $this->driver->execute($sql);
    if (empty($rows)) return array();
    
    $tables = array();
    foreach ($rows as $row) {
      $tables[] = $row["table_name"];
    }
    
    return $tables;
  }
  
  protected function createColumns($tblName)
  {
    $sql = <<<SQL
SELECT
  column_name, data_type,
  is_nullable, column_default, character_maximum_length
  FROM INFORMATION_SCHEMA.COLUMNS
  WHERE table_schema = '{$this->schemaName}' AND table_name = '{$tblName}'
SQL;
    
    $rows = $this->driver->execute($sql);
    if (empty($rows)) return array();
    
    $this->createSequences($tblName);
    $this->createPrimaryKeys($tblName);
    
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
    
    if ($row["data_type"] === "varchar" && $row["character_maximum_length"] === -1) {
      $row["data_type"] = "text";
    } elseif ($row["data_type"] === "float") {
      $row["data_type"] = "double";
    }
    
    Sabel_Db_Type_Manager::create()->applyType($column, $row["data_type"]);
    $this->setDefault($column, $row["column_default"]);
    
    $column->primary = (in_array($column->name, $this->primaryKeys));
    $column->increment = (in_array($column->name, $this->sequences));
    
    if ($column->primary) $column->nullable = false;
    if ($column->isString()) $this->setLength($column, $row);
    
    return $column;
  }
  
  public function getForeignKeys($tblName)
  {
    $sql = <<<SQL
SELECT
  kcu.column_name, kcu2.table_name AS ref_table,
  kcu2.column_name AS ref_column,
  rc.update_rule, rc.delete_rule
  FROM INFORMATION_SCHEMA.TABLE_CONSTRAINTS tc
  INNER JOIN INFORMATION_SCHEMA.KEY_COLUMN_USAGE kcu
    ON kcu.constraint_name = tc.constraint_name
  INNER JOIN INFORMATION_SCHEMA.REFERENTIAL_CONSTRAINTS rc
    ON rc.constraint_name = kcu.constraint_name
  INNER JOIN INFORMATION_SCHEMA.KEY_COLUMN_USAGE kcu2
    ON kcu2.constraint_name = rc.unique_constraint_name
  WHERE
    tc.constraint_type = 'FOREIGN KEY' AND
    kcu.table_schema = '{$this->schemaName}' AND
    kcu.table_name = '{$tblName}';
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
  
  public function getUniques($tblName)
  {
    $sql = <<<SQL
SELECT
  tc.constraint_name, kcu.column_name
  FROM INFORMATION_SCHEMA.TABLE_CONSTRAINTS tc
    INNER JOIN INFORMATION_SCHEMA.KEY_COLUMN_USAGE kcu
      ON tc.constraint_name = kcu.constraint_name
  WHERE tc.table_schema = '{$this->schemaName}'
    AND tc.table_name = '{$tblName}'
    AND tc.constraint_type = 'UNIQUE'
SQL;
    
    $rows = $this->driver->execute($sql);
    if (empty($rows)) return null;
    
    $uniques = array();
    foreach ($rows as $row) {
      $key = $row["constraint_name"];
      $uniques[$key][] = $row["column_name"];
    }
    
    return array_values($uniques);
  }
  
  private function createSequences($tblName)
  {
    if (!empty($this->sequences)) return;
    
    $sql = <<<SQL
SELECT i.name FROM sys.identity_columns i
  INNER JOIN sys.tables t ON t.object_id = i.object_id
  INNER JOIN sys.schemas s ON s.schema_id = t.schema_id
  WHERE t.name = '{$tblName}' and s.name = '{$this->schemaName}';
SQL;
    
    $rows = $this->driver->execute($sql);
    if (!$rows) return;
    
    $seqs =& $this->sequences;
    foreach ($rows as $row) $seqs[] = $row["name"];
  }
  
  private function createPrimaryKeys($tblName)
  {
    if (!empty($this->primaryKeys)) return;
    
    $sql = <<<SQL
SELECT
  column_name
  FROM INFORMATION_SCHEMA.KEY_COLUMN_USAGE
  WHERE table_schema = '{$this->schemaName}'
    AND table_name = '{$tblName}'
    AND constraint_name LIKE 'PK%'
SQL;
    
    $rows = $this->driver->execute($sql);
    if (!$rows) return;
    
    $keys =& $this->primaryKeys;
    foreach ($rows as $row) $keys[] = $row["column_name"];
  }
  
  private function setDefault($column, $default)
  {
    if ($default === null) {
      $column->default = null;
    } else {
      $this->setDefaultValue($column, substr($default, 2, -2));
    }
  }
  
  private function setLength($column, $row)
  {
    $maxlen = $row["character_maximum_length"];
    $column->max = ($maxlen === null) ? 255 : (int)$maxlen;
  }
}
