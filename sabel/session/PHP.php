<?php

/**
 * Sabel_Session_PHP
 *
 * @category   Session
 * @package    org.sabel.session
 * @author     Ebine Yutaka <ebine.yutaka@sabel.jp>
 * @copyright  2004-2008 Mori Reo <mori.reo@sabel.jp>
 * @license    http://www.opensource.org/licenses/bsd-license.php  BSD License
 */
class Sabel_Session_PHP extends Sabel_Session_Abstract
{
  /**
   * @var self
   */
  private static $instance = null;
  
  private function __construct()
  {
    
  }
  
  public static function create()
  {
    if (self::$instance === null) {
      self::$instance = new self();
    }
    
    return self::$instance;
  }
  
  public function start()
  {
    if (!$this->started) {
      session_start();
      
      $this->sessionId = session_id();
      $this->attributes =& $_SESSION;
      
      $this->initialize();
    }
  }
  
  public function setId($sessionId)
  {
    if ($this->started) {
      $message = __METHOD__ . "() the session has already been started.";
      throw new Sabel_Exception_Runtime($message);
    } else {
      session_id($sessionId);
      $this->sessionId = $sessionId;
    }
  }
  
  public function regenerateId()
  {
    if ($this->started) {
      session_regenerate_id(true);
      $this->sessionId = session_id();
    } else {
      $message = __METHOD__ . "() must start the session with start()";
      throw new Sabel_Exception_Runtime($message);
    }
  }
  
  public function destroy()
  {
    if ($this->started) {
      $attributes = $this->attributes;
      session_destroy();
      return $attributes;
    } else {
      $message = __METHOD__ . "() must start the session with start()";
      throw new Sabel_Exception_Runtime($message);
    }
  }
}
