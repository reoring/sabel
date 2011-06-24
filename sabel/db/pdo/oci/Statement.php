<?php

/**
 * Sabel_Db_Pdo_Oci_Statement
 *
 * @category   DB
 * @package    org.sabel.db.pdo
 * @author     Ebine Yutaka <ebine.yutaka@sabel.jp>
 * @copyright  2004-2008 Mori Reo <mori.reo@sabel.jp>
 * @license    http://www.opensource.org/licenses/bsd-license.php  BSD License
 */
class Sabel_Db_Pdo_Oci_Statement extends Sabel_Db_Pdo_Statement
{
  protected $blobs = array();
  
  public function values(array $values)
  {
    $columns = $this->metadata->getColumns();
    foreach ($values as $k => &$v) {
      if (isset($columns[$k]) && $columns[$k]->isBinary()) {
        $this->blobs[$k] = $this->createBlob($v);
        $v = new Sabel_Db_Statement_Expression($this, "EMPTY_BLOB()");
      }
    }
    
    $this->values = $values;
    $this->binds($values);
    
    return $this;
  }
  
  public function clear()
  {
    $this->blobs = array();
    return parent::clear();
  }
  
  public function execute($bindValues = array(), $additionalParameters = array(), $query = null)
  {
    $query = $this->getQuery();
    $blobs = $this->blobs;
    
    if (!empty($blobs) && ($this->isInsert() || $this->isUpdate())) {
      $cols = array();
      $hlds = array();
      foreach ($blobs as $column => $blob) {
        $cols[] = $column;
        $hlds[] = ":" . $column;
        $this->bindValues[$column] = $blob;
      }
      
      $query .= " RETURNING " . implode(", ", $cols) . " INTO " . implode(", ", $hlds);
    }
    
    $this->query = $query;
    $result = parent::execute($bindValues, $additionalParameters);
    
    if (!empty($blobs) && ($this->isInsert() || $this->isUpdate())) {
      foreach ($blobs as $blob) $blob->unlink();
    }
    
    if (!$this->isSelect() || empty($result)) return $result;
    
    // FETCH LOB CONTENTS
    
    $lobColumns = array();
    foreach ($this->metadata->getColumns() as $column) {
      if ($column->isText() || $column->isBinary()) {
        $lobColumns[] = $column->name;
      }
    }
    
    if (!empty($lobColumns)) {
      foreach ($result as &$row) {
        foreach ($lobColumns as $colName) {
          if (isset($row[$colName])) {
            $row[$colName] = stream_get_contents($row[$colName]);
          }
        }
      }
    }
    
    return $result;
  }
  
  public function escape(array $values)
  {
    foreach ($values as &$val) {
      if (is_bool($val)) {
        $val = ($val) ? 1 : 0;
      }
    }
    
    return $values;
  }
  
  public function createBlob($binary)
  {
    return new Sabel_Db_Pdo_Oci_Blob($binary);
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
      $message = "argument must be a string or an array.";
      throw new Sabel_Exception_InvalidArgument($message);
    }
  }
  
  public function createInsertSql()
  {
    if (($column = $this->seqColumn) !== null) {
      $seqName = strtoupper("{$this->table}_{$column}_seq");
      $rows = $this->driver->execute("SELECT {$seqName}.NEXTVAL AS id FROM DUAL");
      $this->values[$column] = $id = $rows[0]["id"];
      $this->bind($column, $id);
      $this->driver->setLastInsertId($id);
    }
    
    return parent::createInsertSql();
  }
  
  protected function createSelectSql()
  {
    $tblName = $this->quoteIdentifier($this->table);
    $projection = $this->getProjection();
    
    $c = $this->constraints;
    $limit  = null;
    $offset = null;
    
    if (isset($c["offset"]) && isset($c["limit"])) {
      $limit  = $c["limit"];
      $offset = $c["offset"];
    } elseif (isset($c["offset"]) && !isset($c["limit"])) {
      $limit  = 100;
      $offset = $c["offset"];
    } elseif (isset($c["limit"]) && !isset($c["offset"])) {
      $limit  = $c["limit"];
      $offset = 0;
    }
    
    if ($limit !== null) {
      if (isset($c["order"])) {
        $order = $c["order"];
      } else {
        $order = convert_to_modelname($this->metadata->getTableName()) . "."
               . $this->metadata->getPrimaryKey() . " ASC";
      }
      
      $orderBy = " ORDER BY " . $this->quoteIdentifierForOrderBy($order);
      $sql = "SELECT * FROM (SELECT ROW_NUMBER() OVER({$orderBy}) \"SDB_RN\", $projection "
           . "FROM $tblName" . $this->join . $this->where . $orderBy . ") "
           . "WHERE \"SDB_RN\" BETWEEN " . ($offset + 1) . " AND " . ($offset + $limit);
    } else {
      $sql = "SELECT $projection FROM $tblName" . $this->join . $this->where;
      
      if (isset($c["order"])) {
        $sql .= " ORDER BY " . $this->quoteIdentifierForOrderBy($c["order"]);
      }
    }
    
    if ($this->forUpdate) {
      $sql .= " FOR UPDATE";
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
