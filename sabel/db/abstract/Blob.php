<?php

/**
 * Sabel_Db_Abstract_Blob
 *
 * @abstract
 * @category   DB
 * @package    org.sabel.db
 * @author     Ebine Yutaka <ebine.yutaka@sabel.jp>
 * @copyright  2004-2008 Mori Reo <mori.reo@sabel.jp>
 * @license    http://www.opensource.org/licenses/bsd-license.php  BSD License
 */
abstract class Sabel_Db_Abstract_Blob extends Sabel_Object
{
  protected $binary = "";
  
  abstract public function getData();
  
  public function __toString()
  {
    return $this->getData();
  }
  
  public function getRawData()
  {
    return $this->binary;
  }
}
