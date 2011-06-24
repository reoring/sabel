<?php

/**
 * @category   KVS
 * @package    org.sabel.kvs
 * @author     Ebine Yutaka <ebine.yutaka@sabel.php-framework.org>
 * @copyright  2004-2008 Mori Reo <mori.reo@sabel.jp>
 * @license    http://www.opensource.org/licenses/bsd-license.php  BSD License
 */
class Sabel_Kvs_Apc extends Sabel_Kvs_Abstract
{
  private static $instance = null;
  
  private function __construct()
  {
    if (extension_loaded("apc")) {
      $this->setKeyPrefix(get_server_name());
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
    $result = apc_fetch($this->genKey($key));
    return ($result === false) ? null : $result;
  }
  
  public function write($key, $value, $timeout = 0)
  {
    apc_store($this->genKey($key), $value, $timeout);
  }
  
  public function delete($key)
  {
    $result = $this->read($key);
    apc_delete($this->genKey($key));
    
    return $result;
  }
}
