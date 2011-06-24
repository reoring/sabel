<?php

/**
 * Sabel_Db_Condition_Manager
 *
 * @category   DB
 * @package    org.sabel.db
 * @author     Ebine Yutaka <yutaka@ebine.org>
 * @copyright  2004-2010 Mori Reo <mori.reo@sabel.jp>
 * @license    http://www.opensource.org/licenses/bsd-license.php  BSD License
 */
class Sabel_Db_Condition_Manager extends Sabel_Object
{
  /**
   * @var array
   */
  protected $conditions = array();
  
  /**
   * @param mixed $condition
   *
   * @throws Sabel_Exception_InvalidArgument
   * @return void
   */
  public function add($condition)
  {
    if ($condition instanceof Sabel_Db_Abstract_Condition) {
      $this->conditions[$condition->getColumn()] = $condition;
    } elseif ($condition instanceof Sabel_Db_Condition_Or ||
              $condition instanceof Sabel_Db_Condition_And) {
      $this->conditions[] = $condition;
    } else {
      $message = "invalid condition object.";
      throw new Sabel_Exception_InvalidArgument($message);
    }
  }
  
  /**
   * @param string $column
   *
   * @return booelan
   */
  public function has($column)
  {
    return isset($this->conditions[$column]);
  }
  
  /**
   * @return booelan
   */
  public function isEmpty()
  {
    return empty($this->conditions);
  }
  
  /**
   * @return array
   */
  public function getConditions()
  {
    return $this->conditions;
  }
  
  /**
   * @param string $key
   * @param mixed  $val
   *
   * @return void
   */
  public function create($key, $val)
  {
    $c = Sabel_Db_Condition::create(Sabel_Db_Condition::EQUAL, $key, $val);
    $this->conditions[$c->getColumn()] = $c;
  }
  
  /**
   * @return array
   */
  public function clear()
  {
    $conditions = $this->conditions;
    $this->conditions = array();
    
    return $conditions;
  }
  
  /**
   * @param Sabel_Db_Statement $stmt
   *
   * @return string
   */
  public function build(Sabel_Db_Statement $stmt)
  {
    if (empty($this->conditions)) return "";
    
    Sabel_Db_Abstract_Condition::rewind();
    
    $query = array();
    foreach ($this->conditions as $condition) {
      $query[] = $condition->build($stmt);
    }
    
    return "WHERE " . implode(" AND ", $query);
  }
}
