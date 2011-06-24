<?php

/**
 * Sabel_Util_Map
 *
 * @category   Util
 * @package    org.sabel.util
 * @author     Ebine Yutaka <ebine.yutaka@sabel.jp>
 * @copyright  2004-2008 Mori Reo <mori.reo@sabel.jp>
 * @license    http://www.opensource.org/licenses/bsd-license.php  BSD License
 */
class Sabel_Util_Map extends Sabel_Object implements Iterator
{
  protected $array = array();
  protected $count = 0;
  protected $index = 0;
  protected $key   = null;
  protected $value = null;
  
  public function __construct($array = null)
  {
    if ($array !== null) {
      if (is_array($array)) {
        $this->array = $array;
        $this->count = count($array);
      } else {
        $message = __METHOD__ . "() argument must be an array.";
        throw new Sabel_Exception_InvalidArgument($message);
      }
    }
  }
  
  public function set($array)
  {
    $this->array = $array;
    $this->count = count($array);
  }
  
  public function isEmpty()
  {
    return empty($this->array);
  }
  
  public function toArray()
  {
    return $this->array;
  }
  
  public function clear()
  {
    $array = $this->array;
    $this->array = array();
    $this->count = 0;
    
    return $array;
  }
  
  public function count()
  {
    return $this->count;
  }
  
  public function put($key, $value)
  {
    $key = $this->convertToString($key);
    $this->array[$key] = $value;
    $this->count++;
    
    return $this;
  }
  
  public function push($value)
  {
    $this->array[] = $value;
    $this->count++;
    
    return $this;
  }
  
  public function get($key)
  {
    $key = $this->convertToString($key);
    
    if (isset($this->array[$key])) {
      return $this->toObject($this->array[$key]);
    } else {
      return null;
    }
  }
  
  public function has($key)
  {
    $key = $this->convertToString($key);
    return isset($this->array[$key]);
  }
  
  public function exists($key)
  {
    $key = $this->convertToString($key);
    return array_key_exists($key, $this->array);
  }
  
  public function keys()
  {
    return array_keys($this->array);
  }
  
  public function values()
  {
    $values = array();
    foreach ($this->array as $value) {
      $values[] = $this->toObject($value);
    }
    
    return $values;
  }
  
  public function remove($key)
  {
    $key = $this->convertToString($key);
    
    if ($this->has($key)) {
      $value = $this->get($key);
      unset($this->array[$key]);
      $this->count--;
      
      return $value;
    }
  }
  
  public function pop()
  {
    if ($this->count === 0) {
      return null;
    } else {
      $value = array_pop($this->array);
      $this->count--;
      
      return $this->toObject($value);
    }
  }
  
  public function shift()
  {
    if ($this->count === 0) {
      return null;
    } else {
      $value = array_shift($this->array);
      $this->count--;
      
      return $this->toObject($value);
    }
  }
  
  public function sort()
  {
    sort($this->array);
    
    return $this;
  }
  
  public function rsort()
  {
    rsort($this->array);
    
    return $this;
  }
  
  public function reverse()
  {
    $this->array = array_reverse($this->array);
    
    return $this;
  }
  
  public function unique()
  {
    $this->array = array_unique($this->array);
    $this->count = count($this->array);
    
    return $this;
  }
  
  public function implode($glue = ", ")
  {
    return new Sabel_Util_String(implode($glue, $this->array));
  }
  
  public function sum()
  {
    return array_sum($this->array);
  }
  
  public function hasMoreElements()
  {
    return $this->valid();
  }
  
  public function nextElement()
  {
    $value = $this->current();
    $this->next();
    
    return $value;
  }
  
  public function search($value, $strict = false)
  {
    $key = array_search($value, $this->array, $strict);
    
    if ($key !== false) {
      $key = new Sabel_Util_String($key);
    }
    
    return $key;
  }
  
  public function merge($array)
  {
    $tarray =& $this->array;
    $count  =& $this->count;
    
    foreach ($array as $key => $val) {
      $tarray[$key] = $val;
      $count++;
    }
    
    return $this;
  }
  
  public function current()
  {
    return $this->value;
  }
  
  public function key()
  {
    return $this->key;
  }
  
  public function valid()
  {
    if ($this->index < $this->count) {
      list ($this->key, $value) = each($this->array);
      $this->value = $this->toObject($value);
      return true;
    } else {
      return false;
    }
  }
  
  public function next()
  {
    $this->index++;
  }
  
  public function rewind()
  {
    $this->reset();
    $this->index = 0;
    $this->count = count($this->array);
  }
  
  public function reset()
  {
    reset($this->array);
  }
  
  protected function convertToString($value)
  {
    if (is_string($value) || is_int($value)) {
      return $value;
    } elseif ($value instanceof Sabel_Object) {
      return $value->toString();
    } elseif (is_object($value) && method_exists($value, "__toString")) {
      return $value->__toString();
    } else {
      $message = __METHOD__ . "() cannot convert the given argument into the string.";
      throw new Sabel_Exception_Runtime($message);
    }
  }
  
  protected function toObject($value)
  {
    if (is_string($value)) {
      return new Sabel_Util_String($value);
    } elseif (is_array($value)) {
      return new Sabel_Util_Map($value);
    } else {
      return $value;
    }
  }
}
