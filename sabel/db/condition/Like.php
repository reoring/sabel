<?php

/**
 * Sabel_Db_Condition_Like
 *
 * @category   DB
 * @package    org.sabel.db
 * @author     Ebine Yutaka <yutaka@ebine.org>
 * @copyright  2004-2010 Mori Reo <mori.reo@sabel.jp>
 * @license    http://www.opensource.org/licenses/bsd-license.php  BSD License
 */
class Sabel_Db_Condition_Like extends Sabel_Db_Abstract_Condition
{
  const STARTS_WITH = 1;
  const BEGINS_WITH = 1;  // alias for STARTS_WITH
  const CONTAINS    = 2;
  const ENDS_WITH   = 3;
  
  /**
   * @var int
   */
  protected $type = Sabel_Db_Condition::LIKE;
  
  /**
   * @var int
   */
  private $likeType = self::BEGINS_WITH;
  
  /**
   * @var boolean
   */
  private $escape = true;
  
  public function type($type)
  {
    if ($type >= 1 && $type <= 3) {
      $this->likeType = $type;
    } else {
      $message = __METHOD__ . "() invalid type.";
      throw new Sabel_Exception_InvalidArgument($message);
    }
    
    return $this;
  }
  
  public function escape($bool)
  {
    if (is_bool($bool)) {
      $this->escape = $bool;
    } else {
      $message = __METHOD__ . "() argument must be a boolean.";
      throw new Sabel_Exception_InvalidArgument($message);
    }
    
    return $this;
  }
  
  public function build(Sabel_Db_Statement $stmt)
  {
    $value = $this->value;
    
    if ($this->escape && (strpos($value, "%") !== false || strpos($value, "_") !== false)) {
      $escapeChars = "ZQXJKVBWYGFPMUCDzqxjkvbwygfpmu";
      
      for ($i = 0; $i < 30; $i++) {
        $esc = $escapeChars{$i};
        if (strpos($value, $esc) === false) {
          $value = preg_replace("/([%_])/", $esc . '$1', $value);
          $like  = "LIKE @ph%d@ escape '{$esc}'";
          break;
        }
      }
    } else {
      $like = "LIKE @ph%d@";
    }
    
    return $this->createQuery($stmt, $value, $like);
  }
  
  private function createQuery($stmt, $value, $part)
  {
    $value = $this->addSpecialCharacter($value);
    $num = ++self::$counter;
    $stmt->bind("ph{$num}", $value);
    
    $column = $this->getQuotedColumn($stmt);
    if ($this->isNot) $column = "NOT " . $column;
    
    return $column . " " . sprintf($part, $num);
  }
  
  private function addSpecialCharacter($value)
  {
    switch ($this->likeType) {
      case self::ENDS_WITH:
        return "%" . $value;
      case self::CONTAINS:
        return "%" . $value . "%";
      default:
        return $value . "%";
    }
  }
}
