<?php

/**
 * Sabel_Db_Finder
 *
 * @category   DB
 * @package    org.sabel.db
 * @author     Ebine Yutaka <ebine.yutaka@sabel.jp>
 * @copyright  2004-2008 Mori Reo <mori.reo@sabel.jp>
 * @license    http://www.opensource.org/licenses/bsd-license.php  BSD License
 */
class Sabel_Db_Finder
{
  protected $model = null;
  protected $join  = null;
  protected $projection = null;
  
  public function __construct($mdlName, $projection = null)
  {
    $this->model = (is_model($mdlName)) ? $mdlName : MODEL($mdlName);
    
    if ($projection !== null) {
      $this->p($projection);
    }
  }
  
  public function getRawInstance()
  {
    return ($this->join === null) ? $this->model : $this->join;
  }
  
  public function p($projection)
  {
    $this->projection = $projection;
    
    return $this;
  }
  
  public function eq($column, $value)
  {
    $this->model->setCondition(eq($column, $value));
    
    return $this;
  }
  
  public function neq($column, $value)
  {
    $this->model->setCondition(neq($column, $value));
    
    return $this;
  }
  
  public function in($column, array $values)
  {
    $this->model->setCondition(in($column, $values));
    
    return $this;
  }
  
  public function nin($column, array $values)
  {
    $this->model->setCondition(nin($column, $values));
    
    return $this;
  }
  
  public function lt($column, $value)
  {
    $this->model->setCondition(lt($column, $value));
    
    return $this;
  }
  
  public function le($column, $value)
  {
    $this->model->setCondition(le($column, $value));
    
    return $this;
  }
  
  public function gt($column, $value)
  {
    $this->model->setCondition(gt($column, $value));
    
    return $this;
  }
  
  public function ge($column, $value)
  {
    $this->model->setCondition(ge($column, $value));
    
    return $this;
  }
  
  public function between($column, $from, $to = null)
  {
    $this->model->setCondition(bw($column, $from, $to));
    
    return $this;
  }
  
  public function bw($column, $from, $to = null)
  {
    return $this->between($column, $from, $to);
  }
  
  public function notBetween($column, $from, $to = null)
  {
    $this->model->setCondition(nbw($column, $from, $to));
    
    return $this;
  }
  
  public function nbw($column, $from, $to = null)
  {
    return $this->notBetween($column, $from, $to);
  }
  
  public function starts($column, $value)
  {
    $this->model->setCondition(starts($column, $value));
    
    return $this;
  }
  
  public function ends($column, $value)
  {
    $this->model->setCondition(ends($column, $value));
    
    return $this;
  }
  
  public function contains($column, $value)
  {
    $this->model->setCondition(contains($column, $value));
    
    return $this;
  }
  
  public function isNull($column)
  {
    $this->model->setCondition(isNull($column));
    
    return $this;
  }
  
  public function isNotNull($column)
  {
    $this->model->setCondition(isNotNull($column));
    
    return $this;
  }
  
  public function where(/* args */)
  {
    foreach (func_get_args() as $condition) {
      $this->model->setCondition($condition);
    }
    
    return $this;
  }
  
  public function w(/* args */)
  {
    $args = func_get_args();
    return call_user_func_array(array($this, "where"), $args);
  }
  
  public function orWhere(/* args */)
  {
    $or = new Sabel_Db_Condition_Or();
    foreach (func_get_args() as $condition) {
      $or->add($condition);
    }
    
    $this->model->setCondition($or);
    
    return $this;
  }
  
  public function ow(/* args */)
  {
    $args = func_get_args();
    return call_user_func_array(array($this, "orWhere"), $args);
  }
  
  public function andWhere(/* args */)
  {
    $and = new Sabel_Db_Condition_And();
    foreach (func_get_args() as $condition) {
      $and->add($condition);
    }
    
    $this->model->setCondition($and);
    
    return $this;
  }
  
  public function aw(/* args */)
  {
    $args = func_get_args();
    return call_user_func_array(array($this, "andWhere"), $args);
  }
  
  public function innerJoin($mdlName, $on = array(), $alias = "")
  {
    $this->_join($mdlName, $on, $alias, "INNER");
    
    return $this;
  }
  
  public function leftJoin($mdlName, $on = array(), $alias = "")
  {
    $this->_join($mdlName, $on, $alias, "LEFT");
    
    return $this;
  }
  
  public function rightJoin($mdlName, $on = array(), $alias = "")
  {
    $this->_join($mdlName, $on, $alias, "RIGHT");
    
    return $this;
  }
  
  protected function _join($mdlName, $on, $alias, $type)
  {
    if ($this->join === null) {
      $this->join = new Sabel_Db_Join($this->model);
    }
    
    $this->join->add($mdlName, $on, $alias, $type);
  }
  
  public function limit($limit)
  {
    $this->model->setLimit($limit);
    
    return $this;
  }
  
  public function offset($offset)
  {
    $this->model->setOffset($offset);
    
    return $this;
  }
  
  public function sort($column, $smode = "ASC", $nulls = "LAST")
  {
    $this->model->setOrderBy($column, $smode, $nulls);
    
    return $this;
  }
  
  public function fetch()
  {
    $this->setProjection();
    
    return $this->getRawInstance()->selectOne();
  }
  
  public function fetchAll()
  {
    $this->setProjection();
    
    return $this->getRawInstance()->select();
  }
  
  public function fetchArray($column = null)
  {
    if ($this->join === null) {
      $model = $this->model;
      
      if ($column === null) {
        $this->setProjection();
        return $model->getRows();
      } else {
        $pkey = $model->getMetadata()->getPrimaryKey();
        if (!is_array($pkey)) $pkey = array($pkey);
        
        $projection = $pkey;
        $projection[] = $column;
        
        $model->setProjection($projection);
        $rows = $model->getRows();
        
        foreach ($rows as $idx => $row) {
          $rows[$idx] = $row[$column];
        }
        
        return $rows;
      }
    } else {
      $message = __METHOD__ . "() can't use fetchArray() on join select.";
      throw new Sabel_Db_Exception($message);
    }
  }
  
  public function count()
  {
    $this->setProjection();
    
    if ($this->join === null) {
      return $this->model->getCount();
    } else {
      return $this->join->getCount(false);
    }
  }
  
  protected function setProjection()
  {
    if ($this->projection !== null) {
      $this->getRawInstance()->setProjection($this->projection);
    }
  }
}
