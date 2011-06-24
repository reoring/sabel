<?php

/**
 * uri candidate
 *
 * @category   Map
 * @package    org.sabel.map
 * @author     Mori Reo <mori.reo@sabel.jp>
 * @copyright  2004-2008 Mori Reo <mori.reo@sabel.jp>
 * @license    http://www.opensource.org/licenses/bsd-license.php  BSD License
 */
class Sabel_Map_Candidate extends Sabel_Object
{
  const MODULE     = "module";
  const CONTROLLER = "controller";
  const ACTION     = "action";
  
  /**
   * @var string
   */
  protected $name = "";
  
  /**
   * @var array
   */
  protected $uriParameters = array();
  
  /**
   * @var array
   */
  protected $destination = array("module" => "", "controller" => "", "action" => "");
  
  public function __construct($name, $uriParameters)
  {
    $this->name = $name;
    
    $reserved = array("module", "controller", "action");
    foreach ($uriParameters as $name => $value) {
      if (in_array($name, $reserved, true)) {
        $this->destination[$name] = $value;
        unset($uriParameters[$name]);
      }
    }
    
    $this->uriParameters = array_map("urldecode", $uriParameters);
  }
  
  public function getUriParameters()
  {
    return $this->uriParameters;
  }
  
  public function getDestination()
  {
    return new Sabel_Map_Destination($this->destination);
  }
  
  public function getName()
  {
    return $this->name;
  }
  
  public function uri($param = "")
  {
    if ($param === null) {
      $param = "";
    } elseif (!is_string($param)) {
      $message = __METHOD__ . "() argument must be a string.";
      throw new Sabel_Exception_InvalidArgument($message);
    }
    
    if ($param !== "" && strpos($param, ",") === false && strpos($param, ":") === false) {
      return ltrim($param, "/");
    }
    
    $parameters = array();
    
    if ($param === "") {
      $parameters = array_merge($this->destination, $this->uriParameters);
    } else {
      foreach (explode(",", $param) as $param) {
        list ($key, $val) = array_map("trim", explode(":", $param));
        if ($key === "n") $key = "name";
        $parameters[$key] = $val;
      }
    }
    
    $name  = (isset($parameters["name"])) ? $parameters["name"] : $this->name;
    $route = Sabel_Map_Configurator::getRoute($name);
    return $route->createUrl($parameters, array_merge($this->destination, $this->uriParameters));
  }
}
