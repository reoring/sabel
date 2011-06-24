<?php

/**
 * Processor_Controller
 *
 * @category   Processor
 * @package    lib.processor
 * @author     Ebine Yutaka <ebine.yutaka@sabel.jp>
 * @copyright  2004-2008 Mori Reo <mori.reo@sabel.jp>
 * @license    http://www.opensource.org/licenses/bsd-license.php  BSD License
 */
class Processor_Controller extends Sabel_Bus_Processor
{
  protected $virtualControllerName = "SabelVirtualController";
  
  public function execute(Sabel_Bus $bus)
  {
    $destination = $bus->get("destination");
    if (($controller = $this->createController($destination)) === null) {
      $controller = $this->createVirtualController();
    }
    
    if ($response = $bus->get("response")) {
      $controller->setResponse($response);
      if ($controller instanceof $this->virtualControllerName) {
        $response->getStatus()->setCode(Sabel_Response::NOT_FOUND);
      }
    }
    
    if ($request = $bus->get("request")) {
      $controller->setRequest($request);
    }
    
    if ($session = $bus->get("session")) {
      $controller->setSession($session);
    }
    
    $bus->set("controller", $controller);
  }
  
  protected function createController($destination)
  {
    list ($module, $controller,) = $destination->toArray();
    $class = ucfirst($module) . "_Controllers_" . ucfirst($controller);
    
    if (Sabel::using($class)) {
      l("create controller '{$class}'");
      return new $class();
    } else {
      l("controller '{$class}' not found", SBL_LOG_WARN);
      return null;
    }
  }
  
  protected function createVirtualController()
  {
    $className = $this->virtualControllerName;
    if (!class_exists($className, false)) {
      eval ("class $className extends Sabel_Controller_Page {}");
    }
    
    l("create virtual controller '{$className}'");
    
    return new $className();
  }
}
