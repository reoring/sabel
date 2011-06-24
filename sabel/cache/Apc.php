<?php

/**
 * Cache implementation of APC
 *
 * @category   Cache
 * @package    org.sabel.cache
 * @author     Mori Reo <mori.reo@sabel.jp>
 * @copyright  2004-2008 Mori Reo <mori.reo@sabel.jp>
 * @license    http://www.opensource.org/licenses/bsd-license.php  BSD License
 */
class Sabel_Cache_Apc implements Sabel_Cache_Interface
{
  private static $instance = null;
  
  /**
   * @var Sabel_Kvs_Apc
   */
  protected $kvs = null;
  
  private function __construct()
  {
    if (extension_loaded("apc")) {
      $this->kvs = Sabel_Kvs_Apc::create();
    } else {
      $message = __METHOD__ . "() apc extension not loaded.";
      throw new Sabel_Exception_Runtime($message);
    }
  }
  
  public static function create()
  {
    if (self::$instance === null) {
      self::$instance = new self();
    }
    
    return self::$instance;
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
