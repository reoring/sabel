<?php

/**
 * Sabel_Db_Oci_Metadata
 *
 * @category   DB
 * @package    org.sabel.db
 * @author     Ebine Yutaka <ebine.yutaka@sabel.jp>
 * @copyright  2004-2008 Mori Reo <mori.reo@sabel.jp>
 * @license    http://www.opensource.org/licenses/bsd-license.php  BSD License
 */
class Sabel_Db_Oci_Metadata extends Sabel_Db_Abstract_Metadata
{
  /**
   * @var array
   */
  private $comments = array();
  
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
    $sql  = "SELECT table_name FROM all_tables WHERE owner = '{$this->schemaName}'";
    $rows = $this->driver->execute($sql);
    if (empty($rows)) return array();
    
    $tables = array();
    foreach ($rows as $row) {
      $tables[] = $row["table_name"];
    }
    
    return array_map("strtolower", $tables);
  }
  
  protected function createColumns($tblName)
  {
    $tblName = strtoupper($tblName);
    
    $sql = <<<SQL
SELECT
  table_name, column_name, data_type, data_precision,
  nullable, data_default, char_length
  FROM all_tab_columns
  WHERE owner = '{$this->schemaName}' AND table_name = '{$tblName}'
SQL;
    
    $rows = $this->driver->execute($sql);
    if (empty($rows)) return array();
    
    $this->createSequences();
    $this->createPrimaryKeys($tblName);
    $this->createComments($tblName);
    
    $columns = array();
    foreach ($rows as $row) {
      $colName = strtolower($row["column_name"]);
      $columns[$colName] = $this->createColumn($row);
    }
    
    return $columns;
  }
  
  protected function createColumn($row)
  {
    $column = new Sabel_Db_Metadata_Column();
    $column->name = strtolower($row["column_name"]);
    $column->nullable = ($row["nullable"] !== "N");
    
    $type = strtolower($row["data_type"]);
    $default = trim($row["data_default"]);
    $precision = (int)$row["data_precision"];
    
    if ($type === "number") {
      if ($precision === 1 && ($default === "1" || $default === "0")) {
        $column->type = Sabel_Db_Type::BOOL;
      } else {
        if ($precision === 5) {
          $type = "smallint";
        } elseif ($precision === 19) {
          $type = "bigint";
        } else {
          $type = "integer";
        }
      }
    }
    
    if (!$column->isBool()) {
      if ($type === "float") {
        $type = ($precision === 24) ? "float" : "double";
      } elseif ($type === "date" && !$this->isDate($column->name)) {
        $type = "datetime";
      }
      
      Sabel_Db_Type_Manager::create()->applyType($column, $type);
    }
    
    $this->setDefault($column, $default);
    
    $seq = $row["table_name"] . "_" . $row["column_name"] . "_seq";
    $column->increment = (in_array(strtoupper($seq), $this->sequences));
    $column->primary   = (in_array($column->name, $this->primaryKeys));
    
    if ($column->primary) {
      $column->nullable = false;
    }
    
    if ($column->isString()) {
      $column->max = (int)$row["char_length"];
    }
    
    return $column;
  }
  
  public function getForeignKeys($tblName)
  {
    $tblName = strtoupper($tblName);
    
    $sql = <<<SQL
SELECT
  acc.column_name, ac2.table_name AS ref_table,
  acc2.column_name AS ref_column, ac.delete_rule
  FROM all_constraints ac
  INNER JOIN all_cons_columns acc
    ON acc.constraint_name = ac.constraint_name
  INNER JOIN all_constraints ac2
    ON ac2.constraint_name = ac.r_constraint_name
  INNER JOIN all_cons_columns acc2
    ON acc2.constraint_name = ac2.constraint_name
  WHERE ac.owner = '{$this->schemaName}'
    AND ac.constraint_type = 'R'
    AND ac.table_name = '{$tblName}'
SQL;
    
    $rows = $this->driver->execute($sql);
    if (empty($rows)) return null;
    
    $columns = array();
    foreach ($rows as $row) {
      $column = strtolower($row["column_name"]);
      $columns[$column]["referenced_table"]  = strtolower($row["ref_table"]);
      $columns[$column]["referenced_column"] = strtolower($row["ref_column"]);
      $columns[$column]["on_delete"]         = $row["delete_rule"];
      $columns[$column]["on_update"]         = "NO ACTION";
    }
    
    return $columns;
  }
  
  public function getUniques($tblName)
  {
    $tblName = strtoupper($tblName);
    
    $sql = <<<SQL
SELECT
  acc.constraint_name, acc.column_name
  FROM all_cons_columns acc
  INNER JOIN all_constraints ac
    ON ac.constraint_name = acc.constraint_name
  WHERE ac.owner = '{$this->schemaName}'
    AND ac.constraint_type = 'U'
    AND acc.table_name = '{$tblName}'
SQL;
    
    $rows = $this->driver->execute($sql);
    if (empty($rows)) return null;
    
    $uniques = array();
    foreach ($rows as $row) {
      $key = $row["constraint_name"];
      $uniques[$key][] = strtolower($row["column_name"]);
    }
    
    return array_values($uniques);
  }
  
  private function createSequences()
  {
    if (!empty($this->sequences)) return;
    
    $sql = "SELECT sequence_name FROM all_sequences "
         . "WHERE sequence_owner = '{$this->schemaName}'";
         
    $seqs =& $this->sequences;
    $rows = $this->driver->execute($sql);
    if (empty($rows)) return;
    
    foreach ($rows as $row) {
      $seqs[] = $row["sequence_name"];
    }
  }
  
  private function createPrimaryKeys($tblName)
  {
    if (!empty($this->primaryKeys)) return;
    
    $sql = <<<SQL
SELECT
  acc.column_name
  FROM all_cons_columns acc
  INNER JOIN all_constraints ac
    ON ac.constraint_name = acc.constraint_name
  WHERE ac.owner = '{$this->schemaName}'
    AND ac.constraint_type = 'P'
    AND acc.table_name = '{$tblName}'
SQL;
    
    $keys =& $this->primaryKeys;
    $rows = $this->driver->execute($sql);
    if (empty($rows)) return;
    
    foreach ($rows as $row) {
      $keys[] = strtolower($row["column_name"]);
    }
  }
  
  private function createComments($tblName)
  {
    $sql = "SELECT column_name, comments FROM all_col_comments "
         . "WHERE owner = '{$this->schemaName}' AND table_name = '{$tblName}'";
         
    $rows = $this->driver->execute($sql);
    if (empty($rows)) return;
    
    foreach ($rows as $row) {
      if (($comment = $row["comments"]) !== null) {
        $colName = strtolower($row["column_name"]);
        $this->comments[$colName] = $comment;
      }
    }
  }
  
  private function setDefault($co, $default)
  {
    $this->setDefaultValue($co, $default);
    if (is_string($co->default) && ($length = strlen($co->default)) > 1) {
      if ($co->default{0} === "'" && substr($co->default, --$length, 1) === "'") {
        $co->default = substr($co->default, 1, --$length);
      }
    }
  }
  
  private function isDate($colName)
  {
    return (isset($this->comments[$colName]) && $this->comments[$colName] === "date");
  }
}
