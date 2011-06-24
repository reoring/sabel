<?php

/**
 * Sabel_Util_HashList
 *
 * @category   Util
 * @package    org.sabel.util
 * @author     Ebine Yutaka <ebine.yutaka@sabel.jp>
 * @copyright  2004-2008 Mori Reo <mori.reo@sabel.jp>
 * @license    http://www.opensource.org/licenses/bsd-license.php  BSD License
 */
class Sabel_Util_HashList extends Sabel_Object
{
  const FIRST = "INDEX_FIRST";
  const LAST  = "INDEX_LAST";
  
  /**
   * @var const self
   */
  private $index = self::FIRST;
  
  /**
   * @var array
   */
  private $hashList = array();
  
  /**
   * @var boolean
   */
  private $iterating = false;
  
  /**
   * @var int
   */
  private $size = 0;
  
  /**
   * @var array
   */
  private $names = array();
  
  /**
   * @var array
   */
  private $values = array();
  
  public function add($name, $value, $force = false)
  {
    $set = $this->has($name);
    
    if (!$force && $set) {
      $message = __METHOD__ . "() '{$name}' already set.";
      throw new Sabel_Exception_Runtime($message);
    } elseif ($set) {
      $this->values[$this->names[$name]] = $value;
    } else {
      $size =& $this->size;
      $this->names[$name]  = $size;
      $this->values[$size] = $value;
      $size++;
    }
  }
  
  public function replace($target, $name, $value)
  {
    if ($this->has($target)) {
      $p = $this->names[$target];
      unset($this->names[$target]);
      $this->values[$p] = $value;
      $this->names[$name] = $p;
    } else {
      $message = __METHOD__ . "() '{$target}' not found.";
      throw new Sabel_Exception_Runtime($message);
    }
  }
  
  public function get($name)
  {
    if (isset($this->names[$name])) {
      return $this->values[$this->names[$name]];
    } else {
      return null;
    }
  }
  
  public function has($name)
  {
    return isset($this->names[$name]);
  }
  
  public function insertPrevious($target, $name, $insertValue)
  {
    if ($this->has($target)) {
      $p = $this->names[$target];
      foreach ($this->names as &$pointer) {
        if ($pointer >= $p) $pointer++;
      }
      
      $this->names[$name] = $p;
      $values = array();
      
      foreach ($this->values as $k => $value) {
        if ($k >= $p) {
          $values[$k + 1] = $value;
        } else {
          $values[$k] = $value;
        }
      }
      
      $values[$p] = $insertValue;
      $this->values = $values;
      $this->size++;
      
      if ($this->iterating && $p <= $this->index) {
        $this->index++;
      }
    } else {
      $message = __METHOD__ . "() '{$target}' not found.";
      throw new Sabel_Exception_Runtime($message);
    }
  }
  
  public function insertNext($target, $name, $insertValue)
  {
    if ($this->has($target)) {
      $p = $this->names[$target];
      foreach ($this->names as &$pointer) {
        if ($pointer > $p) $pointer++;
      }
      
      $this->names[$name] = $p + 1;
      $values = array();
      
      foreach ($this->values as $k => $value) {
        if ($k > $p) {
          $values[$k + 1] = $value;
        } else {
          $values[$k] = $value;
        }
      }
      
      $values[$p + 1] = $insertValue;
      $this->values = $values;
      $this->size++;
      
      if ($this->iterating && ($p + 1) < $this->index) {
        $this->index++;
      }
    } else {
      $message = __METHOD__ . "() '{$target}' not found.";
      throw new Sabel_Exception_Runtime($message);
    }
  }
  
  public function remove($target)
  {
    if ($this->has($target)) {
      $values = $this->values;
      $p = $this->names[$target];
      unset($this->names[$target]);
      $removed = $values[$p];
      unset($values[$p]);
      
      foreach ($this->names as &$pointer) {
        if ($pointer > $p) $pointer--;
      }
      
      ksort($values);
      $this->values = array_values($values);
      $this->size--;
      
      if ($this->iterating && $p <= $this->index) {
        $this->index--;
      }
      
      return $removed;
    } else {
      $message = __METHOD__ . "() '{$target}' not found.";
      throw new Sabel_Exception_Runtime($message);
    }
  }
  
  public function first()
  {
    $this->index = self::FIRST;
  }
  
  public function last()
  {
    $this->index = self::LAST;
  }
  
  public function count()
  {
    return $this->size;
  }
  
  public function next()
  {
    $i = ($this->index === self::FIRST) ? 0 : $this->index + 1;
    
    if (isset($this->values[$i])) {
      $this->iterating = true;
      $this->index = $i;
      return $this->values[$i];
    } else {
      return null;
    }
  }
  
  public function previous()
  {
    $i = ($this->index === self::LAST) ? $this->size - 1 : $this->index - 1;
    
    if (isset($this->values[$i])) {
      $this->iterating = true;
      $this->index = $i;
      return $this->values[$i];
    } else {
      return null;
    }
  }
  
  public function toArray()
  {
    $names  = $this->names;
    $values = $this->values;
    
    asort($names);
    
    $retValue = array();
    foreach ($names as $name => $pointer) {
      $retValue[$name] = $values[$pointer];
    }
    
    return $retValue;
  }
}
