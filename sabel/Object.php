<?php

/**
 * Sabel Object
 *
 * @abstract
 * @category   Core
 * @package    org.sabel.core
 * @author     Mori Reo <mori.reo@sabel.jp>
 * @author     Ebine Yutaka <ebine.yutaka@sabel.jp>
 * @copyright  2004-2008 Mori Reo <mori.reo@sabel.jp>
 * @license    http://www.opensource.org/licenses/bsd-license.php  BSD License
 */
abstract class Sabel_Object
{
  /**
   * @param string $name
   *
   * @return boolean
   */
  public final function hasMethod($name)
  {
    return method_exists($this, $name);
  }
  
  /**
   * @return string
   */
  public function getName()
  {
    return get_class($this);
  }
  
  /**
   * @return string
   */
  public function __toString()
  {
    return $this->hashCode();
  }
  
  /**
   * @param object $object
   *
   * @return boolean
   */
  public function equals($object)
  {
    return ($this == $object);
  }
  
  /**
   * @return string
   */
  public function hashCode()
  {
    return sha1(serialize($this));
  }
  
  /**
   * @return Sabel_Reflection_Class
   */
  public function getReflection()
  {
    return new Sabel_Reflection_Class($this);
  }
  
  /**
   * an alias for __toString()
   *
   * @return string
   */
  public final function toString()
  {
    return $this->__toString();
  }
}
