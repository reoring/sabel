<?php

/**
 * Sabel_Db_Condition_GreaterThan
 *
 * @category   DB
 * @package    org.sabel.db
 * @author     Ebine Yutaka <yutaka@ebine.org>
 * @copyright  2004-2010 Mori Reo <mori.reo@sabel.jp>
 * @license    http://www.opensource.org/licenses/bsd-license.php  BSD License
 */
class Sabel_Db_Condition_GreaterThan extends Sabel_Db_Abstract_Condition
{
  protected $type = Sabel_Db_Condition::GREATER_THAN;
  
  public function build(Sabel_Db_Statement $stmt)
  {
    $num = ++self::$counter;
    $stmt->bind("ph{$num}", $this->value);
    
    $column = $this->getQuotedColumn($stmt);
    if ($this->isNot) $column = "NOT " . $column;
    
    return $column . " > @ph{$num}@";
  }
}
