<?php

/**
 * Sabel_Db_Join_Relation
 *
 * @category   DB
 * @package    org.sabel.db
 * @author     Ebine Yutaka <ebine.yutaka@sabel.jp>
 * @copyright  2004-2008 Mori Reo <mori.reo@sabel.jp>
 * @license    http://www.opensource.org/licenses/bsd-license.php  BSD License
 */
class Sabel_Db_Join_Relation extends Sabel_Db_Join_Base
{
  protected $objects = array();
  
  public function innerJoin($object, $on = array(), $alias = "")
  {
    return $this->add($object, $on, $alias, "INNER");
  }
  
  public function leftJoin($object, $on = array(), $alias = "")
  {
    return $this->add($object, $on, $alias, "LEFT");
  }
  
  public function rightJoin($object, $on = array(), $alias = "")
  {
    return $this->add($object, $on, $alias, "RIGHT");
  }
  
  public function add($object, $on = array(), $alias = "", $type = "")
  {
    if (is_string($object) || is_model($object)) {
      $object = new Sabel_Db_Join_Object($object);
    }
    
    if (!empty($alias)) $object->setAlias($alias);
    if (!empty($type))  $object->setJoinType($type);
    
    $object->setChildName($this->getName());
    
    $structure = Sabel_Db_Join_Structure::getInstance();
    $structure->addJoinObject($this->getName(), $object);
    $this->objects[] = $object;
    
    if (!empty($on)) {
      $object->on($on);
    } elseif ($object->getOn() === array()) {
      $object->on(create_join_key(
        $this->model, $object->getModel()->getTableName()
      ));
    }
    
    return $this;
  }
  
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
    
    foreach ($this->objects as $object) {
      $projection = array_merge($projection, $object->getProjection($stmt));
    }
    
    return $projection;
  }
  
  public function getJoinQuery(Sabel_Db_Statement $stmt)
  {
    $query = $this->_getJoinQuery($stmt);
    
    foreach ($this->objects as $object) {
      $query[] = $object->getJoinQuery($stmt);
    }
    
    return implode("", $query);
  }
}
