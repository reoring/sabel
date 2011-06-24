<?php

/**
 * Sabel_Db_Oci_Statement
 *
 * @category   DB
 * @package    org.sabel.db
 * @author     Ebine Yutaka <ebine.yutaka@sabel.jp>
 * @copyright  2004-2008 Mori Reo <mori.reo@sabel.jp>
 * @license    http://www.opensource.org/licenses/bsd-license.php  BSD License
 */
class Sabel_Db_Oci_Statement extends Sabel_Db_Statement
{
  protected $blobs = array();
  
  public function __construct(Sabel_Db_Oci_Driver $driver)
  {
    $this->driver = $driver;
  }
  
  public function clear()
  {
    $this->blobs = array();
    return parent::clear();
  }
  
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
  
  public function execute($bindValues = array(), $additionalParameters = array(), $query = null)
  {
    $query = $this->getQuery();
    $blobs = $this->blobs;
    
    if (!empty($blobs)) {
      $cols = array();
      $hlds = array();
      foreach (array_keys($blobs) as $column) {
        $cols[] = $column;
        $hlds[] = ":" . $column;
      }
      
      $query .= " RETURNING " . implode(", ", $cols) . " INTO " . implode(", ", $hlds);
    }
    
    $additionalParameters["blob"] = $blobs;
    return parent::execute($bindValues, $additionalParameters, $query);
  }
  
  public function escape(array $values)
  {
    if (extension_loaded("mbstring")) {
      $currentRegexEnc = mb_regex_encoding();
      mb_regex_encoding(mb_internal_encoding());
      
      foreach ($values as $k => &$val) {
        if (is_bool($val)) {
          $val = ($val) ? 1 : 0;
        } elseif (is_string($val)) {
          $val = "'" . mb_ereg_replace("'", "''", $val) . "'";
        }
      }
      
      mb_regex_encoding($currentRegexEnc);
    } else {
      foreach ($values as &$val) {
        if (is_bool($val)) {
          $val = ($val) ? 1 : 0;
        } elseif (is_string($val)) {
          $val = "'" . str_replace("'", "''", $val) . "'";
        }
      }
    }
    
    return $values;
  }
  
  public function createBlob($binary)
  {
    $conn = $this->driver->getConnection();
    return new Sabel_Db_Oci_Blob($conn, $binary);
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
  
  protected function createConstraintSql()
  {
    $sql = "";
    $c = $this->constraints;
    
    if (isset($c["order"])) {
      $sql .= " ORDER BY " . $this->quoteIdentifierForOrderBy($c["order"]);
    }
    
    $limit  = (isset($c["limit"]))  ? $c["limit"]  : null;
    $offset = (isset($c["offset"])) ? $c["offset"] : null;
    
    $this->driver->setLimit($limit);
    $this->driver->setOffset($offset);
    
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
