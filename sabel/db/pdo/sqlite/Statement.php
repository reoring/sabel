<?php

/**
 * Sabel_Db_Pdo_Sqlite_Statement
 *
 * @category   DB
 * @package    org.sabel.db.pdo
 * @author     Ebine Yutaka <ebine.yutaka@sabel.jp>
 * @copyright  2004-2008 Mori Reo <mori.reo@sabel.jp>
 * @license    http://www.opensource.org/licenses/bsd-license.php  BSD License
 */
class Sabel_Db_Pdo_Sqlite_Statement extends Sabel_Db_Pdo_Statement
{
  public function escape(array $values)
  {
    foreach ($values as &$val) {
      if (is_bool($val)) {
        $val = ($val) ? "true" : "false";
      }
    }
    
    return $values;
  }
  
  protected function createSelectSql()
  {
    $tblName = $this->quoteIdentifier($this->table);
    $projection = $this->getProjection();
    
    $sql = "SELECT {$projection} FROM "
         . $this->quoteIdentifier($this->table)
         . $this->join
         . $this->where
         . $this->createConstraintSql();
    
    // Not yet implemented
    // if ($this->forUpdate) $sql .= " FOR UPDATE";
    
    return $sql;
  }
}
