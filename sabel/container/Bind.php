<?php

/**
 * Sabel Container Bind
 *
 * @category   Container
 * @package    org.sabel.container
 * @author     Mori Reo <mori.reo@sabel.jp>
 * @copyright  2004-2008 Mori Reo <mori.reo@sabel.jp>
 * @license    http://www.opensource.org/licenses/bsd-license.php  BSD License
 */
final class Sabel_Container_Bind
{
  private
    $interface,
    $setter,
    $implementation = "";
    
  public function __construct($interface)
  {
    $this->interface = $interface;
  }
  
  public function to($implementation)
  {
    $this->implementation = $implementation;
    
    return $this;
  }
  
  public function setter($methodName)
  {
    $this->setter = trim($methodName);
    
    return $this;
  }
  
  public function hasSetter()
  {
    return !empty($this->setter);
  }
  
  public function getSetter()
  {
    return $this->setter;
  }
  
  public function getImplementation()
  {
    return $this->implementation;
  }
}
