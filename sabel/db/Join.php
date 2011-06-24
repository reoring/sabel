<?php

/**
 * Sabel_Db_Join
 *
 * @category   DB
 * @package    org.sabel.db
 * @author     Ebine Yutaka <ebine.yutaka@sabel.jp>
 * @copyright  2004-2008 Mori Reo <mori.reo@sabel.jp>
 * @license    http://www.opensource.org/licenses/bsd-license.php  BSD License
 */
class Sabel_Db_Join extends Sabel_Object
{
  /**
   * @var Sabel_Db_Model
   */
  protected $model = null;
  
  /**
   * @var array
   */
  protected $projection = array();
  
  /**
   * @var string
   */
  protected $tblName = "";
  
  /**
   * @var object[]
   */
  protected $objects = array();
  
  /**
   * @var Sabel_Db_Join_Structure
   */
  protected $structure = null;
  
  public function __construct($model)
  {
    if (is_string($model)) {
      $model = MODEL($model);
    } elseif (!is_model($model)) {
      $message = __METHOD__ . "() argument must be a string or an instance of model.";
      throw new Sabel_Exception_InvalidArgument($message);
    }
    
    $this->model     = $model;
    $this->tblName   = $model->getTableName();
    $this->structure = Sabel_Db_Join_Structure::getInstance();
  }
  
  public function getModel()
  {
    return $this->model;
  }
  
  public function clear()
  {
    if (is_object($this->structure)) {
      $this->structure->clear();
    }
    
    Sabel_Db_Join_ColumnHash::clear();
  }
  
  public function setProjection(array $projections)
  {
    $this->projection = $projections;
    
    return $this;
  }
  
  public function setCondition($arg1, $arg2 = null)
  {
    $this->model->setCondition($arg1, $arg2);
    
    return $this;
  }
  
  public function setOrderBy($column, $mode = "asc", $nulls = "last")
  {
    $this->model->setOrderBy($column, $mode, $nulls);
    
    return $this;
  }
  
  public function setLimit($limit)
  {
    $this->model->setLimit($limit);
    
    return $this;
  }
  
  public function setOffset($offset)
  {
    $this->model->setOffset($offset);
    
    return $this;
  }
  
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
    
    $object->setChildName($this->tblName);
    
    $this->structure->addJoinObject($this->tblName, $object);
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
  
  public function getCount($clearState = true)
  {
    $stmt = $this->model->prepareStatement(Sabel_Db_Statement::SELECT);
    
    $query = array();
    foreach ($this->objects as $object) {
      $query[] = $object->getJoinQuery($stmt);
    }
    
    $rows = $this->execute($stmt, "COUNT(*) AS cnt", implode("", $query));
    if ($clearState) $this->clear();
    return (int)$rows[0]["cnt"];
  }
  
  public function selectOne()
  {
    $results = $this->select();
    return (isset($results[0])) ? $results[0] : null;
  }
  
  public function select()
  {
    $stmt = $this->model->prepareStatement(Sabel_Db_Statement::SELECT);
    $projection = $this->createProjection($stmt);
    
    $query = array();
    foreach ($this->objects as $object) {
      $query[] = $object->getJoinQuery($stmt);
    }
    
    $results = array();
    if ($rows = $this->execute($stmt, $projection, implode("", $query))) {
      $results = Sabel_Db_Join_Result::build($this->model, $this->structure, $rows);
    }
    
    $this->clear();
    
    return $results;
  }
  
  protected function execute($stmt, $projection, $join)
  {
    $stmt->projection($projection)
         ->where($this->model->getCondition()->build($stmt))
         ->join($join);
    
    $constraints = $this->model->getConstraints();
    return $stmt->constraints($constraints)->execute();
  }
  
  protected function createProjection(Sabel_Db_Statement $stmt)
  {
    if (empty($this->projection)) {
      $projection = array();
      foreach ($this->objects as $object) {
        $projection = array_merge($projection, $object->getProjection($stmt));
      }
      
      $quotedTblName = $stmt->quoteIdentifier($this->tblName);
      foreach ($this->model->getColumnNames() as $column) {
        $projection[] = $quotedTblName . "." . $stmt->quoteIdentifier($column);
      }
    } else {
      $projection = array();
      foreach ($this->projection as $name => $proj) {
        if (($tblName = convert_to_tablename($name)) === $this->tblName) {
          foreach ($proj as $column) {
            $projection[] = $stmt->quoteIdentifier($tblName) . "." . $stmt->quoteIdentifier($column);
          }
        } else {
          foreach ($proj as $column) {
            $as = "{$tblName}.{$column}";
            if (strlen($as) > 30) $as = Sabel_Db_Join_ColumnHash::toHash($as);
            $p = $stmt->quoteIdentifier($tblName) . "." . $stmt->quoteIdentifier($column);
            $projection[] = $p . " AS " . $stmt->quoteIdentifier($as);
          }
        }
      }
    }
    
    return implode(", ", $projection);
  }
}
