<?php

/**
 * cache to null
 *
 * @category   Cache
 * @package    org.sabel.cache
 * @author     Mori Reo <mori.reo@sabel.jp>
 * @copyright  2004-2008 Mori Reo <mori.reo@sabel.jp>
 * @license    http://www.opensource.org/licenses/bsd-license.php  BSD License
 */
class Sabel_Cache_Null implements Sabel_Cache_Interface
{
  private static $instance = null;
  
  public static function create()
  {
    if (self::$instance === null) {
      self::$instance = new self();
    }
    
    return self::$instance;
  }
  
  public function read($key)
  {
    return null;
  }
  
  public function write($key, $value, $timeout = 0)
  {
    
  }
  
  public function delete($key)
  {
    return null;
  }
}
