<?php

class Logics_Result extends Sabel_Object
{
  protected $result = null;
  protected $isSuccess = true;
  
  public function __construct($value)
  {
    $this->set($value);
  }
  
  public function success()
  {
    $this->isSuccess = true;
    
    return $this;
  }
  
  public function isSuccess()
  {
    return $this->isSuccess;
  }
  
  public function failure()
  {
    $this->isSuccess = false;
    
    return $this;
  }
  
  public function isFailure()
  {
    return !$this->isSuccess;
  }
  
  public function set($value)
  {
    $this->result = $value;
    
    return $this;
  }
  
  public function get()
  {
    return $this->result;
  }
  
  public function __toString()
  {
    $r = $this->result;
    
    if (is_object($r)) {
      if ($r instanceof Sabel_Object) {
        return $r->toString();
      } elseif (method_exists($r, "__toString")) {
        return $r->__toString();
      } else {
        $message = "could not be converted to string.";
        throw new Sabel_Exception_Runtime(__METHOD__ . "() " . $message);
      }
    } elseif (is_bool($r)) {
      return ($r) ? "true" : "false";
    } else {
      return (string)$r;
    }
  }
  
  public function isString()
  {
    return is_string($this->result);
  }
  
  public function isInt()
  {
    return is_int($this->result);
  }
  
  public function isFloat()
  {
    return is_float($this->result);
  }
  
  public function isBoolean()
  {
    return is_bool($this->result);
  }
  
  public function isArray()
  {
    return is_array($this->result);
  }
  
  public function isNull()
  {
    return ($this->result === null);
  }
  
  public function isNotNull()
  {
    return ($this->result !== null);
  }
}
