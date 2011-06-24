<?php

/**
 * @interface
 * @category   KVS
 * @package    org.sabel.kvs
 * @author     Ebine Yutaka <ebine.yutaka@sabel.php-framework.org>
 * @copyright  2004-2008 Mori Reo <mori.reo@sabel.jp>
 * @license    http://www.opensource.org/licenses/bsd-license.php  BSD License
 */
interface Sabel_Kvs_Interface
{
  public function read($key);
  public function write($key, $value, $timeout = 0);
  public function delete($key);
}
