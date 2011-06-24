<?php

/**
 * Sabel_Db_Condition_Or
 *
 * @category   DB
 * @package    org.sabel.db
 * @author     Ebine Yutaka <ebine.yutaka@sabel.jp>
 * @copyright  2004-2008 Mori Reo <mori.reo@sabel.jp>
 * @license    http://www.opensource.org/licenses/bsd-license.php  BSD License
 */
class Sabel_Db_Condition_Or extends Sabel_Object
{
  protected $conditions = array();
  
  public function add($condition)
  {
    $this->conditions[] = $condition;
    
    return $this;
  }
  
  public function build(Sabel_Db_Statement $sql)
  {
    $query = array();
    foreach ($this->conditions as $condition) {
      $query[] = $condition->build($sql);
    }
    
    return "(" . implode(" OR ", $query) . ")";
  }
}
