<?php

/**
 * Sabel_Map_Config_Route
 *
 * @category   Map
 * @package    org.sabel.map
 * @author     Mori Reo <mori.reo@sabel.jp>
 * @copyright  2004-2008 Mori Reo <mori.reo@sabel.jp>
 * @license    http://www.opensource.org/licenses/bsd-license.php  BSD License
 */
class Sabel_Map_Config_Route
{
  private $name = "";
  private $uri  = "";
  private $requirements = array();
  private $defaults = array();
  
  private $module = "", $controller = "", $action = "";
  
  public function __construct($name)
  {
    $this->name = $name;
  }
  
  public function uri($uri)
  {
    $this->uri = $uri;
    
    return $this;
  }
  
  public function requirements($requirements)
  {
    $this->requirements = $requirements;
    
    return $this;
  }
  
  public function defaults($defaults)
  {
    $this->defaults = $defaults;
    
    return $this;
  }
  
  public function module($module)
  {
    $this->module = $module;
    
    return $this;
  }
  
  public function controller($controller)
  {
    $this->controller = $controller;
    
    return $this;
  }
  
  public function action($action)
  {
    $this->action = $action;
    
    return $this;
  }
  
  public function getName()
  {
    return $this->name;
  }
  
  public function getUri()
  {
    return $this->uri;
  }
  
  public function getRequirements()
  {
    return $this->requirements;
  }
  
  public function getDefaults()
  {
    return $this->defaults;
  }
  
  public function getDestination()
  {
    $destination = array();
    
    if ($this->module !== "") {
      $destination["module"] = $this->module;
    }
    
    if ($this->controller !== "") {
      $destination["controller"] = $this->controller;
    }
    
    if ($this->action !== "") {
      $destination["action"] = $this->action;
    }
    
    return $destination;
  }
  
  public function createUrl($params, $currentUris = array())
  {
    unset($params["name"]);
    
    $parts = explode("/", $this->uri);
    $firstIndex = null;
    
    foreach ($params as $key => $param) {
      switch ($key) {
        case "m":
          $params["module"] = $param;
          $key = "module";
          unset($params["m"]);
          break;
        
        case "c":
          $params["controller"] = $param;
          $key = "controller";
          unset($params["c"]);
          break;
        
        case "a":
          $params["action"] = $param;
          $key = "action";
          unset($params["a"]);
          break;
      }
      
      if ($firstIndex === null) {
        $firstIndex = array_search(":" . $key, $parts, true);
      }
    }
    
    $i = 0;
    $url = array();
    $defaults = $this->defaults;
    
    foreach ($parts as $name) {
      if ($name{0} !== ":") {
        $url[] = $name;
      } else {
        $key = ltrim($name, ":");
        if (array_isset($key, $params)) {
          $url[] = $params[$key];
        } elseif ($firstIndex !== false && $i >= $firstIndex) {
          if (isset($defaults[$name])) $url[] = $defaults[$name];
        } elseif (array_isset($key, $currentUris)) {
          $url[] = $currentUris[$key];
        }
        
        $i++;
      }
    }
    
    return implode("/", array_map("urlencode", $url));
  }
}
