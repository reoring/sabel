<?php

abstract class Form_Html_Date_Base extends Sabel_Object
{
  protected $name = "";
  protected $value = null;
  protected $timestamp = null;
  
  public function __construct($name, $value = null)
  {
    $this->name  = $name;
    $this->value = $value;
  }
  
  protected function numSelect($type, $name, $start, $end, $includeBlank)
  {
    $html = array('<select name="' . $name . '[' . $type . ']">');
    
    if ($includeBlank) {
      $html[] = '<option value=""></option>';
    }
    
    $val = $this->selectedValue($type);
    
    if ($start > $end) {
      for ($i = $start; $i >= $end; $i--) {
        if ($i === $val) {
          $html[] = '<option value="' . $i . '" selected="selected">' . $i . '</option>';
        } else {
          $html[] = '<option value="' . $i . '">' . $i . '</option>';
        }
      }
    } else {
      for ($i = $start; $i <= $end; $i++) {
        if ($i === $val) {
          $html[] = '<option value="' . $i . '" selected="selected">' . $i . '</option>';
        } else {
          $html[] = '<option value="' . $i . '">' . $i . '</option>';
        }
      }
    }
    
    return implode(PHP_EOL, $html) . PHP_EOL . "</select>";
  }
  
  protected function selectedValue($type)
  {
    if ($this->timestamp === null) {
      return null;
    }
    
    switch ($type) {
      case "y":
        return (int)date("Y", $this->timestamp);
        
      case "m":
        return (int)date("n", $this->timestamp);
        
      case "d":
        return (int)date("j", $this->timestamp);
        
      case "h":
        return (int)date("G", $this->timestamp);
        
      case "i":
        return (int)date("i", $this->timestamp);
        
      case "s":
        return (int)date("s", $this->timestamp);
    }
  }
  
  protected function getYearRange($yearRange)
  {
    if (!is_array($yearRange) && !is_string($yearRange) || is_empty($yearRange)) {
      return array(1970, 2037);
    } elseif (is_array($yearRange)) {
      return $yearRange;
    } elseif (strpos($yearRange, ":")) {
      return explode(":", $yearRange);
    } else {
      $char = $yearRange{0};
      $ny = (int)date("Y");
      
      if ($char === "-") {
        return array($ny + $yearRange, $ny);
      } elseif ($char === "+") {
        return array($ny, $ny + $yearRange);
      } else {
        $t = (int)floor($yearRange / 2);
        return array($ny - $t, $ny + ($yearRange - $t));
      }
    }
  }
}
