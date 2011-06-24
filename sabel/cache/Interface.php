<?php

/**
 * an interface of cache classes.
 *
 * @interface
 * @category   Cache
 * @package    org.sabel.cache
 * @author     Ebine Yutaka <ebine.yutaka@sabel.jp>
 * @copyright  2004-2008 Mori Reo <mori.reo@sabel.jp>
 * @license    http://www.opensource.org/licenses/bsd-license.php  BSD License
 */
interface Sabel_Cache_Interface
{
  public function read($key);
  public function write($key, $value, $timeout = 0);
  public function delete($key);
}
