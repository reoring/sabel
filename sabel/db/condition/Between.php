<?php

/**
 * Sabel_Db_Condition_Between
 *
 * @category   DB
 * @package    org.sabel.db
 * @author     Ebine Yutaka <yutaka@ebine.org>
 * @copyright  2004-2010 Mori Reo <mori.reo@sabel.jp>
 * @license    http://www.opensource.org/licenses/bsd-license.php  BSD License
 */
class Sabel_Db_Condition_Between extends Sabel_Db_Abstract_Condition
{
  protected $type = Sabel_Db_Condition::BETWEEN;
  
  public function build(Sabel_Db_Statement $stmt)
  {
    $f   = ++self::$counter;
    $t   = ++self::$counter;
    $val = $this->value;
    
    $stmt->bind("ph{$f}", $val[0]);
    $stmt->bind("ph{$t}", $val[1]);
    
    $column = $this->getQuotedColumn($stmt);
    if ($this->isNot) $column = "NOT " . $column;
    
    return $column . " BETWEEN @ph{$f}@ AND @ph{$t}@";
  }
}
