<?php

/**
 * @abstract
 * @category   KVS
 * @package    org.sabel.kvs
 * @author     Ebine Yutaka <ebine.yutaka@sabel.php-framework.org>
 * @copyright  2004-2008 Mori Reo <mori.reo@sabel.jp>
 * @license    http://www.opensource.org/licenses/bsd-license.php  BSD License
 */
abstract class Sabel_Kvs_Abstract implements Sabel_Kvs_Interface
{
  protected $prefix = "default";
  
  public function setKeyPrefix($prefix)
  {
    $this->prefix = $prefix;
  }
  
  protected function genKey($key)
  {
    return $this->prefix . "_" . $key;
  }
}
