<?php

class Form_Html_Date_Datetime extends Form_Html_Date_Base
{
  public function toHtml($yearRange, $withSecond, $includeBlank)
  {
    if ($this->value === null) {
      $this->timestamp = ($includeBlank) ? null : time();
    } else {
      $this->timestamp = strtotime($this->value);
    }
    
    $name = $this->name;
    list ($first, $last) = $this->getYearRange($yearRange);
    
    $html   = array();
    $html[] = $this->numSelect("y", $name, $first, $last, $includeBlank);
    $html[] = $this->numSelect("m", $name,      1,    12, $includeBlank);
    $html[] = $this->numSelect("d", $name,      1,    31, $includeBlank);
    $html[] = $this->numSelect("h", $name,      0,    23, $includeBlank);
    $html[] = $this->numSelect("i", $name,      0,    59, $includeBlank);
    
    if ($withSecond) {
      $html[] = $this->numSelect("s", $name, 0, 59, $includeBlank);
    }
    
    return implode("&nbsp;", $html);
  }
}
