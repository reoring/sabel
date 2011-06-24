<?php

/**
 * Sabel_Validator
 *
 * @category   Request
 * @package    org.sabel.request
 * @author     Ebine Yutaka <yutaka@ebine.org>
 * @copyright  2004-2008 Mori Reo <mori.reo@sabel.jp>
 * @license    http://www.opensource.org/licenses/bsd-license.php  BSD License
 */
class Sabel_Validator extends Sabel_Object
{
  /**
   * @var array
   */
  protected $methods = array();
  
  /**
   * @var array
   */
  protected $displayNames = array();
  
  /**
   * @var array
   */
  protected $errors = array();
  
  /**
   * @var array
   */
  protected $validators = array();
  
  public function add($name, $method)
  {
    if (is_array($method)) {
      foreach ($method as $m) {
        $this->add($name, $m);
      }
    } else {
      $v = $this->_parse($method);
      $m = $v["method"];
      $a = $v["arguments"];
      
      if ($m{0} === "-") {
        $this->delete($name, substr($m, 1));
      } else {
        $this->methods[$name][$m] = $a;
      }
    }
    
    return $this;
  }
  
  public function delete($name, $method = null)
  {
    if ($method === null) {
      unset($this->methods[$name]);
    } else {
      unset($this->methods[$name][$method]);
    }
    
    return $this;
  }
  
  public function register($validator)
  {
    if (is_object($validator)) {
      $this->validators[] = $validator;
    } else {
      $message = __METHOD__ . "() argument must be an object.";
      throw new Sabel_Exception_InvalidArgument($message);
    }
  }
  
  public function __call($method, $arguments)
  {
    foreach ($this->validators as $validator) {
      if (method_exists($validator, $method)) {
        return call_user_func_array(array($validator, $method), $arguments);
      }
    }
    
    $message = __METHOD__ . "() Call to undefined method.";
    throw new Sabel_Exception_Runtime($message);
  }
  
  public function hasError()
  {
    return !empty($this->errors);
  }
  
  public function getErrors()
  {
    return $this->errors;
  }
  
  public function setDisplayNames(array $displayNames)
  {
    $this->displayNames = $displayNames;
  }
  
  public function setDisplayName($name, $displayName)
  {
    $this->displayNames[$name] = $displayName;
  }
  
  public function getDisplayName($name)
  {
    if (isset($this->displayNames[$name])) {
      return $this->displayNames[$name];
    } else {
      return $name;
    }
  }
  
  public function validate($values)
  {
    $errors = array();
    $methods = $this->methods;
    
    foreach ($methods as $key => $_methods) {
      if (strpos($key, ":") !== false) {
        if (strpos($key, ",") === false) {
          list ($iname, $dname) = explode(":", $key, 2);
          $methods[$iname] = $_methods;
          $this->displayNames[$iname] = $dname;
          unset($methods[$key]);
        } else {
          $names = array();
          foreach (explode(",", $key) as $k) {
            list ($iname, $dname) = explode(":", $k, 2);
            $names[] = $iname;
            $this->displayNames[$iname] = $dname;
          }
          
          $methods[implode(",", $names)] = $_methods;
          unset($methods[$key]);
        }
      }
    }
    
    foreach (array_keys($methods) as $key) {
      if (strpos($key, ",") === false) {
        if (!isset($values[$key])) {
          $values[$key] = null;
        }
      } else {
        $vs = array();
        foreach (explode(",", $key) as $k) {
          $vs[$k] = (isset($values[$k])) ? $values[$k] : null;
        }
        
        $values[$key] = $vs;
      }
    }
    
    foreach ($values as $key => $value) {
      if (!isset($methods[$key])) continue;
      
      foreach ($methods[$key] as $method => $arguments) {
        if (strpos($key, ",") !== false) {
          $key = explode(",", $key);
        }
        
        if (is_empty($arguments)) {
          if (($message = $this->$method($key, $value)) !== null) {
            $errors[] = $message;
          }
        } else {
          eval('$message = $this->' . $method . '($key, $value, ' . $arguments . ');');
          if ($message !== null) $errors[] = $message;
        }
      }
    }
    
    $this->errors = $errors;
    
    return empty($errors);
  }
  
  public function required($name, $value)
  {
    if (is_object($value) && method_exists($value, "__toString")) {
      $value = $value->__toString();
    }
    
    if (is_empty($value)) {
      return $this->getDisplayName($name) . " is required.";
    }
  }
  
  public function integer($name, $value)
  {
    if (!is_empty($value) && !is_number($value)) {
      return $this->getDisplayName($name) . " must be an integer.";
    }
  }
  
  public function numeric($name, $value)
  {
    if (!is_empty($value) && !is_numeric($value)) {
      return $this->getDisplayName($name) . " must be a numeric.";
    }
  }
  
  public function naturalNumber($name, $value)
  {
    if (!is_empty($value) && !is_natural_number($value)) {
      return $this->getDisplayName($name) . " must be a natural number.";
    }
  }
  
  public function alnum($name, $value)
  {
    if (!is_empty($value) && preg_match('/^[0-9a-zA-Z]+$/', $value) === 0) {
      return $this->getDisplayName($name) . " must be alphanumeric.";
    }
  }
  
  public function strlen($name, $value, $max)
  {
    if (!is_empty($value) && strlen($value) > $max) {
      return $this->getDisplayName($name) . " must be {$max} characters or less.";
    }
  }
  
  public function strwidth($name, $value, $max)
  {
    if (!is_empty($value) && strlen($value) > $max) {
      return $this->getDisplayName($name) . " must be {$max} characters or less.";
    }
  }
  
  public function max($name, $value, $max)
  {
    if (!is_empty($value) && is_number($value) && $value > $max) {
      return $this->getDisplayName($name) . " must be {$max} or less.";
    }
  }
  
  public function min($name, $value, $min)
  {
    if (!is_empty($value) && is_number($value) && $value < $min) {
      return $this->getDisplayName($name) . " must be {$min} or more.";
    }
  }
  
  public function boolean($name, $value)
  {
    if (!is_empty($value) && !in_array($value, array("0", "1", false, true, 0, 1), true)) {
      return "Invalid " . $this->getDisplayName($name) . " foramt.";
    }
  }
  
  public function date($name, $value)
  {
    if (!is_empty($value)) {
      @list ($y, $m, $d) = explode("-", str_replace("/", "-", $value));
      if (!checkdate($m, $d, $y)) {
        return "Invalid " . $this->getDisplayName($name) . " format.";
      }
    }
  }
  
  public function datetime($name, $value)
  {
    if (!is_empty($value)) {
      @list ($date, $time) = explode(" ", str_replace("/", "-", $value));
      @list ($y, $m, $d) = explode("-", $date);
      
      if (!checkdate($m, $d, $y)) {
        return "Invalid " . $this->getDisplayName($name) . " format.";
      } else {
        if (preg_match('/^((0?|1)[\d]|2[0-3]):(0?[\d]|[1-5][\d]):(0?[\d]|[1-5][\d])$/', $time) === 0) {
          return "Invalid " . $this->getDisplayName($name) . " format.";
        }
      }
    }
  }
  
  public function image($name, $value, $size = "300K", $validTypes = array())
  {
    if (!is_empty($value)) {
      $data = null;
      
      if (is_string($value)) {
        $data = $value;
      } elseif (is_object($value) && method_exists($value, "__toString")) {
        $data = $value->__toString();
      }
      
      if (empty($validTypes)) {
        $validTypes = array("jpeg", "gif", "png");
      }
      
      if (!in_array(Sabel_Util_Image::getType($data), $validTypes, true)) {
        return "Invalid " . $this->getDisplayName($name) . " format.";
      } elseif ($size !== null) {
        if (strlen($data) > strtoint($size)) {
          return $this->getDisplayName($name) . " size exceeds {$size}B.";
        }
      }
    }
  }
  
  public function same($names, $values)
  {
    $ns = array();
    $comp = true;
    
    foreach ($names as $name) {
      $ns[] = $this->getDisplayName($name);
      if (is_empty($values[$name])) {
        $comp = false;
      }
    }
    
    if ($comp) {
      if (count(array_unique($values)) !== 1) {
        return implode(", ", $ns) . " are not identical.";
      }
    }
  }
  
  public function nnumber($name, $value)
  {
    return $this->naturalNumber($name, $value);
  }
  
  private function _parse($validator)
  {
    if (is_array($validator)) {
      return $validator;
    } elseif (($_pos = strpos($validator, "(")) === false) {
      return array("method" => $validator, "arguments" => null);
    } else {
      return array(
        "method" => substr($validator, 0, $_pos),
        "arguments" => substr($validator, $_pos + 1, -1)
      );
    }
  }
}
