<?php

/**
 * Sabel_Db_Model_Proxy
 *
 * @category   DB
 * @package    org.sabel.db
 * @author     Ebine Yutaka <ebine.yutaka@sabel.jp>
 * @copyright  2004-2008 Mori Reo <mori.reo@gmail.com>
 * @license    http://www.opensource.org/licenses/bsd-license.php  BSD License
 */
class Sabel_Db_Model_Proxy extends Sabel_Db_Model
{
  public function __construct($mdlName, $id)
  {
    $this->initialize($mdlName);
    
    if ($id !== null) {
      $this->setCondition($id);
      $this->_doSelectOne($this);
    }
  }
}
