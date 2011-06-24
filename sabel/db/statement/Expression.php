<?php

/**
 * Sabel_Db_Statement_Expression
 *
 * @category   DB
 * @package    org.sabel.db
 * @author     Ebine Yutaka <ebine.yutaka@sabel.jp>
 * @copyright  2004-2008 Mori Reo <mori.reo@sabel.jp>
 * @license    http://www.opensource.org/licenses/bsd-license.php  BSD License
 */
class Sabel_Db_Statement_Expression extends Sabel_Object
{
  protected $stmt = null;
  protected $expression = "";
  
  public function __construct(Sabel_Db_Statement $stmt, $expression)
  {
    $this->stmt = $stmt;
    $this->expression = $expression;
  }
  
  public function __toString()
  {
    return $this->getExpression();
  }
  
  public function getExpression()
  {
    // @todo ESC()
    return $this->expression;
  }
}
