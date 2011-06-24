<?php

/**
 * Sabel_Db_Pdo_Pgsql_Statement
 *
 * @category   DB
 * @package    org.sabel.db.pdo
 * @author     Ebine Yutaka <ebine.yutaka@sabel.jp>
 * @copyright  2004-2008 Mori Reo <mori.reo@sabel.jp>
 * @license    http://www.opensource.org/licenses/bsd-license.php  BSD License
 */
class Sabel_Db_Pdo_Pgsql_Statement extends Sabel_Db_Pdo_Statement
{
  public function execute($bindValues = array(), $additionalParameters = array(), $query = null)
  {
    $result = parent::execute($bindValues, $additionalParameters);
    if (!$this->isSelect() || empty($result)) return $result;
    
    // PDO_PGSQL BYTEA HACK
    
    $binColumns = array();
    foreach ($this->metadata->getColumns() as $column) {
      if ($column->isBinary()) $binColumns[] = $column->name;
    }
    
    if (!empty($binColumns)) {
      foreach ($result as &$row) {
        foreach ($binColumns as $colName) {
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
        $val = ($val) ? "t" : "f";
      }
    }
    
    return $values;
  }
}
