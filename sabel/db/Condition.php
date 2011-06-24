<?php

/**
 * Sabel_Db_Condition
 *
 * @category   DB
 * @package    org.sabel.db
 * @author     Ebine Yutaka <ebine.yutaka@sabel.jp>
 * @copyright  2004-2008 Mori Reo <mori.reo@sabel.jp>
 * @license    http://www.opensource.org/licenses/bsd-license.php  BSD License
 */
class Sabel_Db_Condition
{
  const EQUAL         = 1;
  const ISNULL        = 2;
  const ISNOTNULL     = 3;
  const IN            = 4;
  const BETWEEN       = 5;
  const LIKE          = 6;
  const GREATER_EQUAL = 7;
  const GREATER_THAN  = 8;
  const LESS_EQUAL    = 9;
  const LESS_THAN     = 10;
  const DIRECT        = 11;
  
  /**
   * @param const   $type   Sabel_Db_Condition
   * @param string  $column
   * @param mixed   $value
   * @param boolean $not
   *
   * @return Sabel_Db_Abstract_Condition
   */
  public static function create($type, $column, $value = null, $not = false)
  {
    switch ($type) {
      case self::EQUAL:
        $condition = new Sabel_Db_Condition_Equal($column);
        break;
        
      case self::BETWEEN:
        $condition = new Sabel_Db_Condition_Between($column);
        break;
        
      case self::IN:
        $condition = new Sabel_Db_Condition_In($column);
        break;
        
      case self::LIKE:
        $condition = new Sabel_Db_Condition_Like($column);
        break;
        
      case self::ISNULL:
        $condition = new Sabel_Db_Condition_IsNull($column);
        break;
        
      case self::ISNOTNULL:
        $condition = new Sabel_Db_Condition_IsNotNull($column);
        break;
        
      case self::GREATER_EQUAL:
        $condition = new Sabel_Db_Condition_GreaterEqual($column);
        break;
        
      case self::LESS_EQUAL:
        $condition = new Sabel_Db_Condition_LessEqual($column);
        break;
        
      case self::GREATER_THAN:
        $condition = new Sabel_Db_Condition_GreaterThan($column);
        break;
        
      case self::LESS_THAN:
        $condition = new Sabel_Db_Condition_LessThan($column);
        break;
        
      case self::DIRECT:
        $condition = new Sabel_Db_Condition_Direct($column);
        break;
        
      default:
        $message = __METHOD__ . "() invalid condition type.";
        throw new Sabel_Exception_InvalidArgument($message);
    }
    
    $condition->setValue($value)->isNot($not);
    
    return $condition;
  }
}
