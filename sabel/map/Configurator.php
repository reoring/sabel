<?php

/**
 * Map Configurator
 * useful interface of Sabel_Map_Candidate
 *
 * @abstract
 * @category   Map
 * @package    org.sabel.map
 * @author     Mori Reo <mori.reo@sabel.jp>
 * @author     Ebine Yutaka <ebine.yutaka@sabel.jp>
 * @copyright  2004-2008 Mori Reo <mori.reo@sabel.jp>
 * @license    http://www.opensource.org/licenses/bsd-license.php  BSD License
 */
abstract class Sabel_Map_Configurator implements Sabel_Config
{
  protected static $routes = array();
  
  public function route($name)
  {
    return self::$routes[$name] = new Sabel_Map_Config_Route($name);
  }
  
  public function getValidCandidate($requestUri)
  {
    foreach (self::$routes as $route) {
      if ($parmas = $this->createUriParameter($route, $requestUri)) {
        return new Sabel_Map_Candidate($route->getName(), $parmas);
      }
    }
    
    return null;
  }
  
  protected function createUriParameter($route, $requestUri)
  {
    if ($route->getUri() === "*") {  // matchall
      return $route->getDestination();
    }
    
    $defaults = $route->getDefaults();
    $requirements = $route->getRequirements();
    $regex = $this->convertToRegex($route->getUri(), $defaults, $requirements);
    
    preg_match_all($regex, $requestUri, $matches);
    if (empty($matches[0])) return false;
    
    foreach ($matches as $key => $value) {
      if (is_int($key)) {
        unset($matches[$key]);
      } else {
        $matches[$key] = $value[0];
      }
    }
    
    foreach ($defaults as $name => $value) {
      $name = ltrim($name, ":");
      if ($matches[$name] === "") $matches[$name] = $value;
    }
    
    return array_merge($matches, $route->getDestination());
  }

  protected function convertToRegex($pattern, $defaults, $requirements)
  {
    $pattern = strrev($pattern);
    for ($i = 0, $c = count($defaults); $i < $c; $i++) {
      $pattern = "?)" . $pattern;
      $pattern = preg_replace("%(/)([^(])%", '$1($2', $pattern, 1, $count);
      if ($count === 0) $pattern .= "(";
    }
    
    $regex = "@^" . preg_replace('/:(\w+)/', '(?P<$1>[^/]+)', strrev($pattern)) . "$@";
    
    $any = "(\[\^/\]\+)";
    foreach ($requirements as $name => $reg) {
      $regex = preg_replace("@<(" . ltrim($name, ":") . ")>{$any}@", '<$1>' . $reg, $regex);
    }
    
    return preg_replace("@<(module|controller|action)>{$any}@", '<$1>\w+', $regex);
  }
  
  public static function getRoute($name)
  {
    if (isset(self::$routes[$name])) {
      return self::$routes[$name];
    } else {
      return null;
    }
  }
  
  public static function clearRoutes()
  {
    self::$routes = array();
  }
}
