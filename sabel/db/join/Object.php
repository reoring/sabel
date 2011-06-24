<?php

/**
 * Sabel_Db_Join_Object
 *
 * @category   DB
 * @package    org.sabel.db
 * @author     Ebine Yutaka <yutaka@ebine.org>
 * @copyright  2004-2008 Mori Reo <mori.reo@sabel.jp>
 * @license    http://www.opensource.org/licenses/bsd-license.php  BSD License
 */
class Sabel_Db_Join_Object extends Sabel_Db_Join_Base
{
  public function getProjection(Sabel_Db_Statement $stmt)
  {
    $projection = array();
    $name = ($this->hasAlias()) ? strtolower($this->aliasName) : $this->getName(false);
    
    foreach ($this->columns as $column) {
      $as = "{$name}.{$column}";
      if (strlen($as) > 30) $as = Sabel_Db_Join_ColumnHash::toHash($as);
      $p = $stmt->quoteIdentifier($name) . "." . $stmt->quoteIdentifier($column);
      $projection[] = $p . " AS " . $stmt->quoteIdentifier($as);
    }
    
    return $projection;
  }
  
  public function getJoinQuery(Sabel_Db_Statement $stmt)
  {
    return implode("", $this->_getJoinQuery($stmt));
  }
}
