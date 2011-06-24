<?php

/**
 * Sabel_Db_Pgsql_Metadata
 *
 * @category   DB
 * @package    org.sabel.db
 * @author     Ebine Yutaka <ebine.yutaka@sabel.jp>
 * @copyright  2004-2008 Mori Reo <mori.reo@sabel.jp>
 * @license    http://www.opensource.org/licenses/bsd-license.php  BSD License
 */
class Sabel_Db_Pgsql_Metadata extends Sabel_Db_Abstract_Metadata
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
  table_name, column_name, data_type,
  is_nullable, column_default, character_maximum_length
  FROM information_schema.columns
  WHERE table_schema = '{$this->schemaName}' AND table_name = '{$tblName}'
  ORDER BY ordinal_position ASC
SQL;
    
    $rows = $this->driver->execute($sql);
    if (empty($rows)) return array();
    
    $this->createSequences();
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
    Sabel_Db_Type_Manager::create()->applyType($column, $row["data_type"]);
    $this->setDefault($column, $row["column_default"]);
    
    $column->primary = (in_array($column->name, $this->primaryKeys));
    $seq = $row["table_name"] . "_" . $column->name . "_seq";
    $column->increment = (in_array($seq, $this->sequences));
    
    if ($column->primary) $column->nullable = false;
    if ($column->isString()) $this->setLength($column, $row);
    
    return $column;
  }
  
  public function getForeignKeys($tblName)
  {
    /*
    $sql = <<<SQL
SELECT
  kcu.column_name, ccu.table_name AS ref_table,
  ccu.column_name AS ref_column, rc.delete_rule, rc.update_rule
  FROM information_schema.table_constraints tc
    INNER JOIN information_schema.constraint_column_usage ccu
    ON tc.constraint_name = ccu.constraint_name
    INNER JOIN information_schema.key_column_usage kcu
    ON tc.constraint_name = kcu.constraint_name
    INNER JOIN information_schema.referential_constraints rc
    ON tc.constraint_name = rc.constraint_name
  WHERE tc.table_schema = '{$this->schemaName}'
    AND tc.table_name = '{$tblName}'
    AND tc.constraint_type = 'FOREIGN KEY'
SQL;
    
    $rows = $this->driver->execute($sql);
    if (empty($rows)) return null;
    */
    
    $sql = <<<SQL
SELECT
  constraint_name
  FROM information_schema.table_constraints
  WHERE table_schema = '{$this->schemaName}'
    AND table_name = '{$tblName}'
    AND constraint_type = 'FOREIGN KEY'
SQL;
    
    $cnames = $this->driver->execute($sql);
    if (empty($cnames)) return null;
    
    $fmt = <<<SQL
SELECT * FROM
  (SELECT
     column_name
     FROM information_schema.key_column_usage
     WHERE constraint_name = '%1\$s') AS kcu,
  (SELECT
     table_name AS ref_table, column_name AS ref_column
     FROM information_schema.constraint_column_usage
     WHERE constraint_name = '%1\$s') AS ccu,
  (SELECT
     delete_rule, update_rule
     FROM information_schema.referential_constraints
     WHERE constraint_name = '%1\$s') AS rc
SQL;
    
    $columns = array();
    foreach ($cnames as $cname) {
      foreach ($this->driver->execute(sprintf($fmt, $cname["constraint_name"])) as $row) {
        $column = $row["column_name"];
        $columns[$column]["referenced_table"]  = $row["ref_table"];
        $columns[$column]["referenced_column"] = $row["ref_column"];
        $columns[$column]["on_delete"]         = $row["delete_rule"];
        $columns[$column]["on_update"]         = $row["update_rule"];
      }
    }
    
    return $columns;
  }
  
  public function getUniques($tblName)
  {
    $sql = <<<SQL
SELECT
  tc.constraint_name, kcu.column_name
  FROM information_schema.table_constraints tc
    INNER JOIN information_schema.key_column_usage kcu
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
  
  private function createSequences()
  {
    if (!empty($this->sequences)) return;
    
    $seqs =& $this->sequences;
    $sql  = "SELECT relname FROM pg_statio_user_sequences";
    $rows = $this->driver->execute($sql);
    if (!$rows) return;
    
    foreach ($rows as $row) $seqs[] = $row["relname"];
  }
  
  private function createPrimaryKeys($tblName)
  {
    if (!empty($this->primaryKeys)) return;
    
    $sql = <<<SQL
SELECT
  column_name
  FROM information_schema.key_column_usage
  WHERE table_schema = '{$this->schemaName}'
    AND table_name = '{$tblName}'
    AND constraint_name LIKE '%\_pkey'
SQL;
    
    $keys =& $this->primaryKeys;
    $rows = $this->driver->execute($sql);
    if (!$rows) return;
    
    foreach ($rows as $row) $keys[] = $row["column_name"];
  }
  
  private function setDefault($column, $default)
  {
    if (strpos($default, "nextval") !== false) {
      $column->default = null;
    } else {
      if (($pos = strpos($default, "::")) !== false) {
        $default = substr($default, 0, $pos);
        if ($default{0} === "'") {
          $default = substr($default, 1, -1);
        }
      }
      
      $this->setDefaultValue($column, $default);
    }
  }
  
  private function setLength($column, $row)
  {
    $maxlen = $row["character_maximum_length"];
    $column->max = ($maxlen === null) ? 255 : (int)$maxlen;
  }
}
