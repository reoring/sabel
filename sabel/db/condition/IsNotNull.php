<?php

/**
 * Sabel_Db_Condition_IsNotNull
 *
 * @category   DB
 * @package    org.sabel.db
 * @author     Ebine Yutaka <ebine.yutaka@sabel.jp>
 * @copyright  2004-2008 Mori Reo <mori.reo@sabel.jp>
 * @license    http://www.opensource.org/licenses/bsd-license.php  BSD License
 */
class Sabel_Db_Condition_IsNotNull extends Sabel_Db_Abstract_Condition
{
  protected $type = Sabel_Db_Condition::ISNOTNULL;
  
  public function build(Sabel_Db_Statement $stmt)
  {
    return $this->getQuotedColumn($stmt) . " IS NOT NULL";
  }
}
