<?php

/**
 * Sabel_Session_Abstract
 *
 * @abstract
 * @category   Session
 * @package    org.sabel.session
 * @author     Ebine Yutaka <ebine.yutaka@sabel.jp>
 * @copyright  2004-2008 Mori Reo <mori.reo@sabel.jp>
 * @license    http://www.opensource.org/licenses/bsd-license.php  BSD License
 */
abstract class Sabel_Session_Abstract extends Sabel_Object implements Sabel_Session
{
  const CLIENT_ID_KEY = "sbl_client_id";
  
  /**
   * @var string
   */
  protected $sessionId = "";
  
  /**
   * @var boolean
   */
  protected $started = false;
  
  /**
   * @var boolean
   */
  protected $cookieEnabled = false;
  
  /**
   * @var array
   */
  protected $attributes = array();
  
  /**
   * @var array
   */
  protected $timeouts = array();
  
  protected function initialize()
  {
    $this->started = true;
    $this->cookieEnabled = isset($_COOKIE[session_name()]);
    
    if (!isset($this->attributes[self::CLIENT_ID_KEY])) {
      $this->write(self::CLIENT_ID_KEY, $this->createClientId());
    }
    
    $time = time();
    foreach ($this->attributes as $k => $values) {
      if (($timeout = $values["timeout"]) === 0) continue;
      if ($time >= $timeout) {
        $this->timeouts[$k] = $values;
        unset($this->attributes[$k]);
      }
    }
  }
  
  public function isStarted()
  {
    return $this->started;
  }
  
  public function getName()
  {
    return session_name();
  }
  
  public function getId()
  {
    return $this->sessionId;
  }
  
  public function getClientId()
  {
    return $this->read(self::CLIENT_ID_KEY);
  }
  
  public function isCookieEnabled()
  {
    return $this->cookieEnabled;
  }
  
  public function has($key)
  {
    return isset($this->attributes[$key]);
  }
  
  public function read($key)
  {
    if (isset($this->attributes[$key])) {
      return $this->attributes[$key]["value"];
    } else {
      return null;
    }
  }
  
  public function write($key, $value, $timeout = 0)
  {
    if ($timeout < 0) {
      $message = "timeout value should be 0 or more.";
      throw new Sabel_Exception_InvalidArgument($message);
    }
    
    if ($timeout !== 0) $timeout = time() + $timeout;
    $this->attributes[$key] = array("value" => $value, "timeout" => $timeout);
  }
  
  public function delete($key)
  {
    if (isset($this->attributes[$key])) {
      $value = $this->attributes[$key]["value"];
      unset($this->attributes[$key]);
      return $value;
    } else {
      return null;
    }
  }
  
  public function getTimeouts()
  {
    return $this->timeouts;
  }
  
  protected function createSessionId()
  {
    return (ini_get("session.hash_function") === "1") ? sha1hash() : md5hash();
  }
  
  protected function createClientId()
  {
    if (defined("SESSION_CLIENT_ID_HASH_FUNC")) {
      $func = strtolower(SESSION_CLIENT_ID_HASH_FUNC);
    } else {
      $func = "md5";
    }
    
    if ($func === "sha256") {
      return bin2hex(mhash(MHASH_SHA256, uniqid(mt_rand(), true)));
    } else {
      return $func(uniqid(mt_rand(), true));
    }
  }
}
