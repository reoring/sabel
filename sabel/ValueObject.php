<?php

/**
 * Sabel_ValueObject
 *
 * @category   Core
 * @package    org.sabel.core
 * @author     Ebine Yutaka <ebine.yutaka@sabel.jp>
 * @copyright  2004-2008 Mori Reo <mori.reo@sabel.jp>
 * @license    http://www.opensource.org/licenses/bsd-license.php  BSD License
 */
class Sabel_ValueObject extends Sabel_Object
{
  protected $values = array();
  
  public static function fromArray(array $values)
  {
    $self = new self();
    $self->addValues($values);
    
    return $self;
  }
  
  public function set($key, $value)
  {
    $this->values[$key] = $value;
    
    return $this;
  }
  
  public function __set($key, $value)
  {
    $this->set($key, $value);
  }
  
  public function get($key)
  {
    if (isset($this->values[$key])) {
      return $this->values[$key];
    } else {
      return null;
    }
  }
  
  public function __get($key)
  {
    return $this->get($key);
  }
  
  public function __isset($key)
  {
    return ($this->get($key) !== null);
  }
  
  public function has($key)
  {
    return isset($this->values[$key]);
  }
  
  public function exists($key)
  {
    return array_key_exists($key, $this->values);
  }
  
  public function remove($key)
  {
    $ret = $this->get($key);
    unset($this->values[$key]);
    
    return $ret;
  }
  
  public function addValues(array $values)
  {
    foreach ($values as $key => $val) {
      $this->set($key, $val);
    }
    
    return $this;
  }
  
  public function getValues()
  {
    return $this->values;
  }
  
  public function toArray()
  {
    return $this->getValues();
  }
  
  public function merge($values)
  {
    if (is_array($values)) {
      $this->addValues($values);
    } elseif ($values instanceof self) {
      $this->addValues($values->toArray());
    } else {
      $message = __METHOD__ . "() argument must be an array or "
               . "an instanceof Sabel_ValueObject";
      
      throw new Sabel_Exception_InvalidArgument($message);
    }
    
    return $this;
  }
}
