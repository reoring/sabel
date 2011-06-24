<?php

/**
 * @category   KVS
 * @package    org.sabel.kvs
 * @author     Ebine Yutaka <ebine.yutaka@sabel.php-framework.org>
 * @copyright  2004-2008 Mori Reo <mori.reo@sabel.jp>
 * @license    http://www.opensource.org/licenses/bsd-license.php  BSD License
 */
class Sabel_Kvs_Memcache extends Sabel_Kvs_Abstract
{
  private static $instances = array();
  
  /**
   * @var Memcache
   */
  protected $memcache = null;
  
  private function __construct($host, $port)
  {
    if (extension_loaded("memcache")) {
      $this->memcache = new Memcache();
      $this->addServer($host, $port);
      $this->setKeyPrefix(get_server_name());
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
    $this->memcache->addServer($host, $port, true, $weight);
  }
  
  public function read($key)
  {
    $result = $this->memcache->get($this->genKey($key));
    return ($result === false) ? null : $result;
  }
  
  public function write($key, $value, $timeout = 0, $comp = false)
  {
    $this->memcache->set($this->genKey($key), $value, $comp, $timeout);
  }
  
  public function delete($key)
  {
    $result = $this->read($key);
    $this->memcache->delete($this->genKey($key), 0);
    
    return $result;
  }
}
