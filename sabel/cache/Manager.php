<?php

/**
 * Cache Manager
 *
 * @category   Cache
 * @package    org.sabel.cache
 * @author     Mori Reo <mori.reo@sabel.jp>
 * @copyright  2004-2008 Mori Reo <mori.reo@sabel.jp>
 * @license    http://www.opensource.org/licenses/bsd-license.php  BSD License
 */
class Sabel_Cache_Manager
{
  private static $support = array();
  private static $initialized = false;
  
  public static function init()
  {
    if (self::$initialized) return;
    
    self::$support["apc"]      = extension_loaded("apc");
    self::$support["memcache"] = extension_loaded("memcache");
    
    self::$initialized = true;
  }
  
  public static function getUsableCache()
  {
    self::init();
    
    $instance = null;
    
    if (!defined("ENVIRONMENT") || ENVIRONMENT !== PRODUCTION) {
      $instance = Sabel_Cache_Null::create();
    } elseif (self::$support["apc"]) {
      $instance = Sabel_Cache_Apc::create();
    } elseif (self::$support["memcache"]) {
      $instance = Sabel_Cache_Memcache::create();
    } else {
      $instance = Sabel_Cache_File::create();
    }
    
    return $instance;
  }
}
