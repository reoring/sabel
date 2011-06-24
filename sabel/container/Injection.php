<?php

/**
 * Sabel Container
 *
 * @abstract
 * @category   Container
 * @package    org.sabel.container
 * @author     Mori Reo <mori.reo@sabel.jp>
 * @copyright  2004-2008 Mori Reo <mori.reo@sabel.jp>
 * @license    http://www.opensource.org/licenses/bsd-license.php  BSD License
 */
abstract class Sabel_Container_Injection implements Sabel_Config
{
  protected
    $binds      = array(),
    $aspects    = array(),
    $lifecycle  = array(),
    $constructs = array();
  
  /**
   * bind interface to implementation
   *
   * @param string $interface name of interface
   * @return Sabel_Container_Bind
   */
  public function bind($interface)
  {
    $bind = new Sabel_Container_Bind($interface);
    $this->binds[$interface][] = $bind;
    
    return $bind;
  }
  
  /**
   * bind constructer with object or value
   *
   * @param string $interface name of interface
   * @return Sabel_Container_Bind
   */
  public function construct($className)
  {
    $construct = new Sabel_Container_Construct($className);
    $this->constructs[$className] = $construct;
    
    return $construct;
  }
  
  /**
   * bind aspect
   *
   * @param string $interface name of interface
   * @return Sabel_Container_Bind
   */
  public function aspect($className)
  {    
    $aspect = new Sabel_Container_Aspect($className);
    $this->aspects[$className] = $aspect;
    
    return $aspect;
  }
  
  public function getAspect($className)
  {
    if ($this->hasAspect($className)) {
      return $this->aspects[$className];
    } else {
      return false;
    }
  }
  
  public function getAspects()
  {
    if (is_array($this->aspects)) {
      return array_values($this->aspects);  
    } else {
      return array();
    }
  }
  
  public function hasAspect($className)
  {
    return isset($this->aspects[$className]);
  }
  
  public function hasConstruct($className)
  {
    return array_key_exists($className, $this->constructs);
  }
  
  public function getConstruct($className)
  {
    if (isset($this->constructs[$className])) {
      return $this->constructs[$className];
    } else {
      return false;
    }
  }
  
  public function getBinds()
  {
    return $this->binds;
  }
  
  public function hasBinds()
  {
    return (count($this->binds) >= 1);
  }
  
  public function hasBind($className)
  {
    return isset($this->binds[$className]);
  }
  
  public function getBind($className)
  {
    if ($this->hasBind($className)) {
      return $this->binds[$className];
    } else {
      return false;
    }
  }
}
