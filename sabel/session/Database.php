<?php

/**
 * Sabel_Session_Database
 *
 * @category   Session
 * @package    org.sabel.session
 * @author     Ebine Yutaka <ebine.yutaka@sabel.jp>
 * @copyright  2004-2008 Mori Reo <mori.reo@sabel.jp>
 * @license    http://www.opensource.org/licenses/bsd-license.php  BSD License
 */
class Sabel_Session_Database extends Sabel_Session_Ext
{
  /**
   * @var self
   */
  private static $instance = null;
  
  /**
   * @var boolean
   */
  protected $newSession = false;
  
  /**
   * @var string
   */
  protected $mdlName = "";
  
  /**
   * @var Sabel_Db_Model[]
   */
  protected $models = array();
  
  private function __construct($mdlName)
  {
    $this->mdlName = $mdlName;
    $this->readSessionSettings();
  }
  
  public static function create($mdlName = "SblSession")
  {
    if (self::$instance === null) {
      self::$instance = new self($mdlName);
      register_shutdown_function(array(self::$instance, "destruct"));
    }
    
    return self::$instance;
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
    $this->gc();
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
    $session = $this->getSessionModel($this->sessionId);
    
    if ($session->isSelected()) {
      $session->update(array("sid" => $newId));
    }
    
    $this->sessionId = $newId;
    $this->setSessionIdToCookie($newId);
  }
  
  public function destroy()
  {
    if (!$this->started) {
      $message = __METHOD__ . "() must start the session with start().";
      throw new Sabel_Exception_Runtime($message);
    }
    
    MODEL($this->mdlName)->delete($this->sessionId);
    
    $data = $this->attributes;
    $this->attributes = array();
    
    return $data;
  }
  
  public function destruct()
  {
    if (!$this->started || $this->newSession && empty($this->attributes)) {
      return;
    }
    
    $session = $this->getSessionModel($this->sessionId);
    
    if (!$session->isSelected()) {
      $session->sid = $this->sessionId;
    }
    
    $session->data = str_replace("\000", "\\000", serialize($this->attributes));
    $session->timeout = time() + $this->maxLifetime;
    $session->save();
  }
  
  protected function getSessionData($sessionId)
  {
    $session = $this->getSessionModel($sessionId);
    
    if (!$session->isSelected()) {
      $this->newSession = true;
      return array();
    } elseif ($session->timeout <= now()) {
      return array();
    } else {
      return unserialize(str_replace("\\000", "\000", $session->data));
    }
  }
  
  protected function gc()
  {
    $divisor = ini_get("session.gc_divisor");
    $probability = ini_get("session.gc_probability");
    
    if (empty($divisor)) $divisor = 100;
    if (empty($probability)) $probability = 1;
    
    if (rand(1, $divisor) <= $probability) {
      $model = MODEL($this->mdlName);
      $model->setCondition(Sabel_Db_Condition::create(
        Sabel_Db_Condition::LESS_EQUAL, "timeout", time()
      ));
      
      $model->delete();
    }
  }
  
  protected function getSessionModel($sessionId)
  {
    if (!isset($this->models[$sessionId])) {
      $this->models[$sessionId] = MODEL($this->mdlName, $sessionId);
    }
    
    return $this->models[$sessionId];
  }
}
