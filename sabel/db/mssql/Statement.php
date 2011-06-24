<?php

/**
 * Sabel_Db_Mssql_Statement
 *
 * @category   DB
 * @package    org.sabel.db
 * @author     Ebine Yutaka <ebine.yutaka@sabel.jp>
 * @copyright  2004-2008 Mori Reo <mori.reo@sabel.jp>
 * @license    http://www.opensource.org/licenses/bsd-license.php  BSD License
 */
class Sabel_Db_Mssql_Statement extends Sabel_Db_Statement
{
  public function __construct(Sabel_Db_Mssql_Driver $driver)
  {
    $this->driver = $driver;
  }
  
  public function execute($bindValues = array(), $additionalParameters = array())
  {
    $result = parent::execute($bindValues);
    
    if (!$this->isSelect() || empty($result) || !extension_loaded("mbstring")) {
      return $result;
    }
    
    $fromEnc = (defined("SDB_MSSQL_ENCODING")) ? SDB_MSSQL_ENCODING : "SJIS";
    $toEnc   = mb_internal_encoding();
    $columns = $this->metadata->getColumns();
    
    foreach ($result as &$row) {
      foreach ($columns as $name => $column) {
        if (isset($row[$name]) && ($column->isString() || $column->isText())) {
          $row[$name] = mb_convert_encoding($row[$name], $toEnc, $fromEnc);
        }
      }
    }
    
    return $result;
  }
  
  public function escape(array $values)
  {
    if (extension_loaded("mbstring")) {
      $toEnc = (defined("SDB_MSSQL_ENCODING")) ? SDB_MSSQL_ENCODING : "SJIS";
      $fromEnc = mb_internal_encoding();
      
      $currentRegexEnc = mb_regex_encoding();
      mb_regex_encoding($fromEnc);
      
      foreach ($values as $k => &$val) {
        if (is_bool($val)) {
          $val = ($val) ? "1" : "0";
        } elseif (is_string($val)) {
          $val = "'" . mb_convert_encoding(mb_ereg_replace("'", "''", $val), $toEnc, $fromEnc) . "'";
        }
      }
      
      mb_regex_encoding($currentRegexEnc);
    } else {
      foreach ($values as &$val) {
        if (is_bool($val)) {
          $val = ($val) ? "1" : "0";
        } elseif (is_string($val)) {
          $val = "'" . str_replace("'", "''", $val) . "'";
        }
      }
    }
    
    return $values;
  }
  
  public function createBlob($binary)
  {
    return new Sabel_Db_Mssql_Blob($binary);
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
      $sql = "SELECT * FROM (SELECT ROW_NUMBER() OVER({$orderBy}) AS [SBL_RN], $projection "
           . "FROM $tblName" . $this->join . $this->where . ") AS [SBL_TMP] "
           . "WHERE [SBL_RN] BETWEEN " . ($offset + 1) . " AND "
           . ($offset + $limit) . " " . $orderBy;
    } else {
      $sql = "SELECT $projection FROM $tblName" . $this->join . $this->where;
      
      if (isset($c["order"])) {
        $sql .= " ORDER BY " . $this->quoteIdentifierForOrderBy($c["order"]);
      }
    }
    
    return $sql;
  }
  
  public function quoteIdentifier($arg)
  {
    if (is_array($arg)) {
      foreach ($arg as &$v) {
        $v = '[' . $v . ']';
      }
      return $arg;
    } elseif (is_string($arg)) {
      return '[' . $arg . ']';
    } else {
      $message = __METHOD__ . "() argument must be a string or an array.";
      throw new Sabel_Exception_InvalidArgument($message);
    }
  }
  
  // @todo nulls
  protected function quoteIdentifierForOrderBy($orders)
  {
    $results = array();
    foreach ($orders as $column => $order) {
      $mode  = strtoupper($order["mode"]);
      //$nulls = strtoupper($order["nulls"]);
      
      if (($pos = strpos($column, ".")) !== false) {
        $tblName = convert_to_tablename(substr($column, 0, $pos));
        $column  = $this->quoteIdentifier($tblName) . "."
                 . $this->quoteIdentifier(substr($column, $pos + 1));
      } else {
        $column = $this->quoteIdentifier($column);
      }
      
      //$_nulls    = ($nulls === "FIRST") ? "IS NOT NULL" : "IS NULL";
      $results[] = "{$column} {$mode}";
    }
    
    return implode(", ", $results);
  }
}
