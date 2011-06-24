<?php

/**
 * Sabel_Logger
 *
 * @category   Logger
 * @package    org.sabel.logger
 * @author     Mori Reo <mori.reo@sabel.jp>
 * @copyright  2004-2008 Mori Reo <mori.reo@sabel.jp>
 * @license    http://www.opensource.org/licenses/bsd-license.php  BSD License
 */
class Sabel_Logger extends Sabel_Object
{
  /**
   * @var self
   */
  private static $instance = null;
  
  /**
   * @var Sabel_Logger_Interface[]
   */
  protected $loggers  = array();
  
  /**
   * @var array
   */
  protected $messages = array();
  
  /**
   * @var boolean
   */
  protected $realtime = false;
  
  public static function create()
  {
    if (self::$instance === null) {
      self::$instance = new self();
      self::$instance->addLogger(new Sabel_Logger_File());
      register_shutdown_function(array(self::$instance, "output"));
    }
    
    return self::$instance;
  }
  
  public function addLogger(Sabel_Logger_Interface $logger)
  {
    $this->loggers[] = $logger;
  }
  
  public function realtime($bool)
  {
    $this->realtime = $bool;
    
    if ($bool) {
      $this->output(true);
      $this->messages = array();
    }
  }
  
  public function write($text, $level = SBL_LOG_INFO, $identifier = "default")
  {
    if ((SBL_LOG_LEVEL & $level) === 0) return;
    
    $message = array("time" => now(), "level" => $level, "message" => $text);
    
    if ($this->realtime) {
      $this->_write($identifier, $message);
    } else {
      if (array_key_exists($identifier, $this->messages)) {
        $this->messages[$identifier][] = $message;
      } else {
        $this->messages[$identifier] = array($message);
      }
    }
  }
  
  public function getMessages()
  {
    return $this->messages;
  }
  
  public function output($force = false)
  {
    if ($force || !$this->realtime) {
      foreach ($this->loggers as $logger) {
        $logger->output($this->messages);
      }
    }
  }
  
  protected function _write($identifier, array $message)
  {
    foreach ($this->loggers as $logger) {
      $logger->write($identifier, $message);
    }
  }
}
