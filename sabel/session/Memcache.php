<?php

/**
 * Sabel_Session_Memcache
 *
 * @category   Session
 * @package    org.sabel.session
 * @author     Ebine Yutaka <ebine.yutaka@sabel.jp>
 * @copyright  2004-2008 Mori Reo <mori.reo@sabel.jp>
 * @license    http://www.opensource.org/licenses/bsd-license.php  BSD License
 */
class Sabel_Session_Memcache extends Sabel_Session_Ext
{
  /**
   * @var self[]
   */
  private static $instances = array();
  
  /**
   * @var Sabel_Kvs_Memcache
   */
  protected $kvs = null;
  
  /**
   * @var boolean
   */
  protected $newSession = false;
  
  private function __construct($host, $port)
  {
    if (extension_loaded("memcache")) {
      $this->kvs = Sabel_Kvs_Memcache::create($host, $port);
      $this->readSessionSettings();
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
    
    $instance = self::$instances[$host][$port] = new self($host, $port);
    register_shutdown_function(array($instance, "destruct"));
    
    return $instance;
  }
  
  public function addServer($host, $port = 11211, $weight = 1)
  {
    $this->kvs->addServer($host, $port, true, $weight);
  }
  
  public function start()
  {
    if ($this->started) {
      return;
    }
    
    if (!$sessionId = $this->initSession()) {
      return;
    }
    
    if (is_empty($this->sessionId)) {
      $this->sessionId = $sessionId;
    }
    
    $this->attributes = $this->getSessionData($sessionId);
    
    $this->initialize();
  }
  
  public function setId($sessionId)
  {
    if ($this->started) {
      $message = __METHOD__ . "() the session has already been started.";
      throw new Sabel_Exception_Runtime($message);
    } else {
      $this->sessionId = $sessionId;
    }
  }
  
  public function regenerateId()
  {
    if (!$this->started) {
      $message = __METHOD__ . "() must start the session with start()";
      throw new Sabel_Exception_Runtime($message);
    }
    
    $newId = $this->createSessionId();
    $this->kvs->delete($this->sessionId);
    $this->kvs->write($newId, $this->attributes, $this->maxLifetime);
    $this->sessionId = $newId;
    $this->setSessionIdToCookie($newId);
  }
  
  public function destroy()
  {
    if (!$this->started) {
      $message = __METHOD__ . "() must start the session with start()";
      throw new Sabel_Exception_Runtime($message);
    }
    
    $this->kvs->delete($this->sessionId);
    
    $attributes = $this->attributes;
    $this->attributes = array();
    
    return $attributes;
  }
  
  protected function getSessionData($sessionId)
  {
    $data = $this->kvs->read($sessionId);
    
    if (is_array($data)) {
      return $data;
    } else {
      $this->newSession = true;
      return array();
    }
  }
  
  public function destruct()
  {
    if ($this->started && (!$this->newSession || !empty($this->attributes))) {
      $this->kvs->write($this->sessionId, $this->attributes, $this->maxLifetime);
    }
  }
}
