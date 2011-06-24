<?php

/**
 * Sabel_Db_Ibase_Statement
 *
 * @category   DB
 * @package    org.sabel.db
 * @author     Ebine Yutaka <ebine.yutaka@sabel.jp>
 * @copyright  2004-2008 Mori Reo <mori.reo@sabel.jp>
 * @license    http://www.opensource.org/licenses/bsd-license.php  BSD License
 */
class Sabel_Db_Ibase_Statement extends Sabel_Db_Statement
{
  public function __construct(Sabel_Db_Ibase_Driver $driver)
  {
    $this->driver = $driver;
  }
  
  public function values(array $values)
  {
    $columns = $this->metadata->getColumns();
    
    foreach ($values as $k => &$v) {
      if (isset($columns[$k]) && $columns[$k]->isBinary()) {
        $v = $this->createBlob($v);
      }
    }
    
    $this->values = $values;
    $this->binds($values);
    
    return $this;
  }
  
  public function escape(array $values)
  {
    foreach ($values as &$val) {
      if (is_bool($val)) $val = ($val) ? 1 : 0;
    }
    
    return $values;
  }
  
  public function createBlob($binary)
  {
    return new Sabel_Db_Ibase_Blob($binary);
  }
  
  protected function createSelectSql()
  {
    $sql = "SELECT ";
    $c = $this->constraints;
    
    if (isset($c["limit"])) {
      $query  = "FIRST {$c["limit"]} ";
      $query .= (isset($c["offset"])) ? "SKIP " . $c["offset"] : "SKIP 0";
      $sql   .= $query . " ";
    } elseif (isset($c["offset"])) {
      $sql   .= "SKIP " . $c["offset"] . " ";
    }
    
    $projection = $this->getProjection();
    $sql .= "$projection FROM "
          . $this->quoteIdentifier($this->table)
          . $this->join . $this->where
          . $this->createConstraintSql();
    
    if ($this->forUpdate) {
      $sql .= " FOR UPDATE";
    }
    
    return $sql;
  }
  
  public function createInsertSql()
  {
    if (($column = $this->seqColumn) !== null) {
      $seqName = strtoupper("{$this->table}_{$column}_seq");
      $rows = $this->driver->execute("SELECT GEN_ID({$seqName}, 1) AS id FROM RDB\$DATABASE");
      $this->values[$column] = $id = $rows[0]["id"];
      $this->bind($column, $id);
      $this->driver->setLastInsertId($id);
    }
    
    return parent::createInsertSql();
  }
  
  public function quoteIdentifier($arg)
  {
    if (is_array($arg)) {
      foreach ($arg as &$v) {
        $v = '"' . strtoupper($v) . '"';
      }
      return $arg;
    } elseif (is_string($arg)) {
      return '"' . strtoupper($arg) . '"';
    } else {
      $message = __METHOD__ . "() argument must be a string or an array.";
      throw new Sabel_Exception_InvalidArgument($message);
    }
  }
  
  protected function createConstraintSql()
  {
    $sql = "";
    $c = $this->constraints;
    
    if (isset($c["order"])) {
      $sql .= " ORDER BY " . $this->quoteIdentifierForOrderBy($c["order"]);
    }
    
    return $sql;
  }
  
  protected function quoteIdentifierForOrderBy($orders)
  {
    $results = array();
    foreach ($orders as $column => $order) {
      $mode  = strtoupper($order["mode"]);
      $nulls = strtoupper($order["nulls"]);
      
      if (($pos = strpos($column, ".")) !== false) {
        $tblName = convert_to_tablename(substr($column, 0, $pos));
        $column  = $this->quoteIdentifier($tblName) . "."
                 . $this->quoteIdentifier(substr($column, $pos + 1));
      } else {
        $column = $this->quoteIdentifier($column);
      }
      
      $results[] = "{$column} {$mode} NULLS {$nulls}";
    }
    
    return implode(", ", $results);
  }
}
