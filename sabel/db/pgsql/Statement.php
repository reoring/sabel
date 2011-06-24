<?php

/**
 * Sabel_Db_Pgsql_Statement
 *
 * @category   DB
 * @package    org.sabel.db
 * @author     Ebine Yutaka <ebine.yutaka@sabel.jp>
 * @copyright  2004-2008 Mori Reo <mori.reo@sabel.jp>
 * @license    http://www.opensource.org/licenses/bsd-license.php  BSD License
 */
class Sabel_Db_Pgsql_Statement extends Sabel_Db_Statement
{
  protected $binaries = array();
  
  public function __construct(Sabel_Db_Pgsql_Driver $driver)
  {
    $this->driver = $driver;
  }
  
  public function clear()
  {
    $this->binaries = array();
    return parent::clear();
  }
  
  public function values(array $values)
  {
    $columns = $this->metadata->getColumns();
    
    foreach ($values as $k => &$v) {
      if (isset($columns[$k]) && $columns[$k]->isBinary()) {
        $this->binaries[] = $this->createBlob($v);
        $v = new Sabel_Db_Statement_Expression($this, "__sbl_binary" . count($this->binaries));
      }
    }
    
    $this->values = $values;
    $this->binds($values);
    
    return $this;
  }
  
  public function escape(array $values)
  {
    $conn = $this->driver->getConnection();
    
    foreach ($values as &$val) {
      if (is_bool($val)) {
        $val = ($val) ? "'t'" : "'f'";
      } elseif (is_string($val)) {
        $val = "'" . pg_escape_string($conn, $val) . "'";
      }
    }
    
    return $values;
  }
  
  public function execute($bindValues = array(), $additionalParameters = array(), $query = null)
  {
    $query = $this->getQuery();
    
    if (!empty($this->binaries)) {
      for ($i = 0, $c = count($this->binaries); $i < $c; $i++) {
        $query = str_replace("__sbl_binary" . ($i + 1),
                             $this->binaries[$i]->getData(),
                             $query);
      }
    }
    
    $result = parent::execute($bindValues, $additionalParameters, $query);
    if (!$this->isSelect() || empty($result)) return $result;
    
    $binaryColumns = array();
    foreach ($this->metadata->getColumns() as $column) {
      if ($column->isBinary()) $binaryColumns[] = $column->name;
    }
    
    if (!empty($binaryColumns)) {
      foreach ($result as &$row) {
        foreach ($binaryColumns as $colName) {
          if (isset($row[$colName])) {
            $row[$colName] = pg_unescape_bytea($row[$colName]);
          }
        }
      }
    }
    
    return $result;
  }
  
  public function createBlob($binary)
  {
    $conn = $this->driver->getConnection();
    return new Sabel_Db_Pgsql_Blob($conn, $binary);
  }
}
