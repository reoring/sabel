<?php

/**
 * Sabel_Bus
 *
 * @category   Bus
 * @package    org.sabel.bus
 * @author     Mori Reo <mori.reo@sabel.jp>
 * @copyright  2004-2008 Mori Reo <mori.reo@sabel.jp>
 * @license    http://www.opensource.org/licenses/bsd-license.php  BSD License
 */
class Sabel_Bus extends Sabel_Object
{
  /**
   * @var Sabel_Util_HashList
   */
  protected $processorList = null;
  
  /**
   * @var Sabel_Config[]
   */
  protected $configs = array();
  
  /**
   * @var string[]
   */
  protected $interfaces = array();
  
  /**
   * @var array
   */
  protected $holder  = array();
  
  /**
   * @var object[]
   */
  protected $beforeEvent = array();
  
  /**
   * @var object[]
   */
  protected $afterEvent  = array();
  
  public function __construct()
  {
    $this->processorList = new Sabel_Util_HashList();
    Sabel_Context::getContext()->setBus($this);
  }
  
  public static function create(array $data = array())
  {
    $bus = new self();
    if (!empty($data)) $bus->holder = $data;
    
    return $bus;
  }
  
  public function set($key, $value)
  {
    if (isset($this->interfaces[$key]) && !$value instanceof $this->interfaces[$key]) {
      $message = __METHOD__ . "() '{$key}' must be an instance of " . $this->interfaces[$key];
      throw new Sabel_Exception_Runtime($message);
    }
    
    $this->holder[$key] = $value;
  }
  
  public function get($key)
  {
    if (array_key_exists($key, $this->holder)) {
      return $this->holder[$key];
    } else {
      return null;
    }
  }
  
  /**
   * check bus has a data
   * 
   * @param mixed string or array
   * @return bool
   */
  public function has($key)
  {
    if (is_array($key)) {
      foreach ($key as $k) {
        if (!$this->has($k)) return false;
      }
      
      return true;
    } else {
      return isset($this->holder[$key]);
    }
  }
  
  public function setConfig($name, Sabel_Config $config)
  {
    $this->configs[$name] = $config;
  }
  
  public function getConfig($name)
  {
    if (isset($this->configs[$name])) {
      return $this->configs[$name];
    } else {
      return null;
    }
  }
  
  public function getProcessor($name)
  {
    return $this->processorList->get($name);
  }
  
  public function getProcessorList()
  {
    return $this->processorList;
  }
  
  public function run(Sabel_Bus_Config $config)
  {
    foreach ($config->getProcessors() as $name => $className) {
      $this->addProcessor(new $className($name));
    }
    
    foreach ($config->getConfigs() as $name => $className) {
      $this->setConfig($name, new $className());
    }
    
    $this->interfaces = $config->getInterfaces();
    $logging = $config->isLogging();
    
    try {
      $logger = Sabel_Logger::create();
      $processorList = $this->processorList;
      
      while ($processor = $processorList->next()) {
        $processorName = $processor->name;
        
        $beforeEvents = $this->beforeEvent;
        if (isset($beforeEvents[$processorName])) {
          foreach ($beforeEvents[$processorName] as $event) {
            if ($logging) {
              $logger->write("Bus: beforeEvent " . $event->object->getName() . "::" . $event->method . "()");
            }
            
            $event->object->{$event->method}($this);
          }
        }
        
        if ($logging) {
          $logger->write("Bus: execute " . $processor->name);
        }
        
        $processor->execute($this);
        
        $afterEvents = $this->afterEvent;
        if (isset($afterEvents[$processorName])) {
          foreach ($afterEvents[$processorName] as $event) {
            if ($logging) {
              $logger->write("Bus: afterEvent " . $event->object->getName() . "::" . $event->method . "()");
            }
            
            $event->object->{$event->method}($this);
          }
        }
      }
      
      $processorList->first();
      while ($processor = $processorList->next()) {
        if ($logging) {
          $logger->write("Bus: shutdown " . $processor->name);
        }
        
        $processor->shutdown($this);
      }
      
      return $this->get("result");
    } catch (Exception $e) {
      $message = get_class($e) . ": " . $e->getMessage();
      $logger->write($message);
      
      return ((ENVIRONMENT & DEVELOPMENT) > 0) ? $message : "";
    }
  }
  
  /**
   * add processor to bus.
   *
   * @param Sabel_Bus_Processor $processor
   * @return Sabel_Bus
   */
  public function addProcessor(Sabel_Bus_Processor $processor)
  {
    $this->processorList->add($processor->name, $processor);
    $this->setupEvents($processor);
    
    return $this;
  }
  
  public function insertProcessor($target, Sabel_Bus_Processor $processor, $on)
  {
    if ($on !== "before" && $on !== "after") {
      $message = __METHOD__ . "() argument 3 must be 'before' or 'after'.";
      throw new Sabel_Exception_InvalidArgument($message);
    }
    
    $insertMethod = ($on === "before") ? "insertPrevious" : "insertNext";
    $this->processorList->$insertMethod($target, $processor->name, $processor);
    $this->setupEvents($processor);
    
    return $this;
  }
  
  public function attachExecuteBeforeEvent($processorName, $object, $method)
  {
    $this->attachEvent($processorName, $object, $method, "before");
  }
  
  public function attachExecuteAfterEvent($processorName, $object, $method)
  {
    $this->attachEvent($processorName, $object, $method, "after");
  }
  
  protected function attachEvent($processorName, $object, $method, $when)
  {
    $evt = new stdClass();
    $evt->object = $object;
    $evt->method = $method;
    
    $var = $when . "Event";
    $events =& $this->$var;
    if (isset($events[$processorName])) {
      $events[$processorName][] = $evt;
    } else {
      $events[$processorName] = array($evt);
    }
  }
  
  protected function setupEvents(Sabel_Bus_Processor $processor)
  {
    if ($beforeEvents = $processor->getBeforeEvents()) {
      foreach ($beforeEvents as $target => $callback) {
        $this->attachExecuteBeforeEvent($target, $processor, $callback);
      }
    }
    
    if ($afterEvents = $processor->getAfterEvents()) {
      foreach ($afterEvents as $target => $callback) {
        $this->attachExecuteAfterEvent($target, $processor, $callback);
      }
    }
  }
}
