<?php

/**
 * Cache implementation of Memcache
 *
 * @category   Cache
 * @package    org.sabel.cache
 * @author     Mori Reo <mori.reo@sabel.jp>
 * @copyright  2004-2008 Mori Reo <mori.reo@sabel.jp>
 * @license    http://www.opensource.org/licenses/bsd-license.php  BSD License
 */
class Sabel_Cache_Memcache implements Sabel_Cache_Interface
{
  private static $instances = array();
  
  /**
   * @var Sabel_Kvs_Memcache
   */
  protected $kvs = null;
  
  private function __construct($host, $port)
  {
    if (extension_loaded("memcache")) {
      $this->kvs = Sabel_Kvs_Memcache::create($host, $port);
    } else {
      $message = __METHOD__ . "() memcache extension not loaded.";
      throw new Sabel_Exception_Runtime($message);
    }
  }
  
  public static function create($host = "localhost", $port = 11211)
  {
    if (isset(self::$instances[$host][$port])) {
      return self::$instances[$host][$port];
    }
    
    return self::$instances[$host][$port] = new self($host, $port);
  }
  
  public function addServer($host, $port = 11211, $weight = 1)
  {
    $this->kvs->addServer($host, $port, true, $weight);
  }
  
  public function read($key)
  {
    return $this->kvs->read($key);
  }
  
  public function write($key, $value, $timeout = 0)
  {
    $this->kvs->write($key, $value, $timeout);
  }
  
  public function delete($key)
  {
    return $this->kvs->delete($key);
  }
}
