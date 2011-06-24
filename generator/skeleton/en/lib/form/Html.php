<?php

class Form_Html extends Sabel_Object
{
  /**
   * @var string
   */
  protected $name = "";
  
  /**
   * @var string
   */
  protected $value = null;
  
  /**
   * @var string
   */
  protected $attributes  = "";
  
  /**
   * @param string $name
   *
   * @throws Sabel_Exception_InvalidArgument
   * @return self
   */
  public function setName($name)
  {
    if (is_string($name)) {
      $this->name = $name;
    } else {
      $message = __METHOD__ . "() argument must be a string.";
      throw new Sabel_Exception_InvalidArgument($message);
    }
    
    return $this;
  }
  
  /**
   * @param mixed $value
   *
   * @return self
   */
  public function setValue($value)
  {
    $this->value = $value;
    
    return $this;
  }
  
  /**
   * @param string $attrs
   *
   * @throws Sabel_Exception_InvalidArgument
   * @return self
   */
  public function setAttributes($attributes)
  {
    if (is_string($attributes)) {
      $this->attributes = $attributes;
    } else {
      $message = __METHOD__ . "() argument must be a string.";
      throw new Sabel_Exception_InvalidArgument($message);
    }
    
    return $this;
  }
  
  /**
   * @return self
   */
  public function clear()
  {
    $this->name = "";
    $this->value = null;
    $this->attributes = "";
    
    return $this;
  }
  
  public function text()
  {
    $html  = $this->openTag("input") . 'type="text" ';
    $html .= 'name="' . $this->name . '" value="' . $this->value . '" />';
    
    return $html;
  }
  
  public function password()
  {
    $html  = $this->openTag("input") . 'type="password" ';
    $html .= 'name="' . $this->name . '" value="' . $this->value . '" />';
    
    return $html;
  }
  
  public function textarea()
  {
    $html  = $this->openTag("textarea");
    $html .= 'name="' . $this->name . '">' . $this->value . '</textarea>';
    
    return $html;
  }
  
  public function hidden()
  {
    $html  = $this->openTag("input") . 'type="hidden" ';
    $html .= 'name="' . $this->name . '" value="' . $this->value . '" />';
    
    return $html;
  }
  
  public function file()
  {
    return $this->openTag("input") . 'type="file" name="' . $this->name . '" />';
  }
  
  public function select($data)
  {
    $options = array();
    $value   = $this->value;
    
    foreach ($data as $v => $text) {
      if (!is_empty($value) && $v == $value) {
        $tag = '<option value="' . htmlescape($v) . '" selected="selected">';
      } else {
        $tag = '<option value="' . htmlescape($v) . '">';
      }
      
      $options[] = $tag . htmlescape($text) . '</option>';
    }
    
    $html = $this->openTag("select") . 'name="' . $this->name . '">';
    return $html . implode(PHP_EOL, $options) . PHP_EOL . "</select>";
  }
  
  public function radio($data)
  {
    static $rdonm = 0;
    
    $count = 0;
    $html  = array();
    $name  = $this->name;
    $value = $this->value;
    
    // remove id.
    $attrs = preg_replace('/(^id="[^"]*"| id="[^"]*")/', '', $this->attributes);
    
    foreach ($data as $v => $text) {
      $_id    = "radio_" . $rdonm++;
      $radio  = $this->openTag("input", 'id="' . $_id . '" ' . $attrs) . 'type="radio" ';
      $radio .= 'name="' . $name . '" value="' . htmlescape($v) . '"';
      
      if ($count === 0 && is_empty($value) || !is_empty($value) && $v == $value) {
        $radio .= ' checked="checked"';
      }
      
      $radio .= ' /><label for="' . $_id . '">' . htmlescape($text) . '</label>';
      $html[] = $radio;
      
      $count++;
    }
    
    return implode("&nbsp;" . PHP_EOL, $html);
  }
  
  public function checkbox($data)
  {
    static $chknm = 0;
    
    $html  = array();
    $name  = $this->name;
    $value = $this->value;
    
    if (!is_array($value)) {
      $value = array();
    }
    
    // remove id.
    $attrs = preg_replace('/(^id="[^"]*"| id="[^"]*")/', '', $this->attributes);
    
    foreach ($data as $v => $text) {
      $_id    = "checkbox_" . $chknm++;
      $check  = $this->openTag("input", 'id="' . $_id . '" ' . $attrs) . 'type="checkbox" ';
      $check .= 'name="' . $name . '[]" value="' . htmlescape($v) . '"';
      
      if (!is_empty($value) && in_array($v, $value)) {
        $check .= ' checked="checked"';
      }
      
      $check .= ' /><label for="' . $_id . '">' . htmlescape($text) . '</label>';
      $html[] = $check;
    }
    
    return implode("&nbsp;" . PHP_EOL, $html);
  }
  
  public function datetime($yearRange, $withSecond, $includeBlank)
  {
    $datetime = new Form_Html_Date_Datetime($this->name, $this->value);
    return $datetime->toHtml($yearRange, $withSecond, $includeBlank);
  }
  
  public function date($yearRange, $includeBlank)
  {
    $date = new Form_Html_Date_Object($this->name, $this->value);
    return $date->toHtml($yearRange, $includeBlank);
  }
  
  protected function openTag($tagName, $attributes = null)
  {
    if ($attributes === null) {
      $attributes = $this->attributes;
    }
    
    if (is_empty($attributes)) {
      return "<{$tagName} ";
    } else {
      return "<{$tagName} {$attributes} ";
    }
  }
}
