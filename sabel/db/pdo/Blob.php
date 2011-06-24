<?php

/**
 * Sabel_Db_Pdo_Blob
 *
 * @category   DB
 * @package    org.sabel.db
 * @author     Ebine Yutaka <ebine.yutaka@sabel.jp>
 * @copyright  2004-2008 Mori Reo <mori.reo@sabel.jp>
 * @license    http://www.opensource.org/licenses/bsd-license.php  BSD License
 */
class Sabel_Db_Pdo_Blob extends Sabel_Db_Abstract_Blob
{
  public function __construct($binary)
  {
    $this->binary = $binary;
  }
  
  public function getData()
  {
    return $this->binary;
  }
}
