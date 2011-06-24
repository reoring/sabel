<?php

class Form_Object extends Sabel_ValueObject
{
  /**
   * @var string
   */
  protected $nameSpace = "";
  
  /**
   * @var array
   */
  protected $displayNames = array();
  
  /**
   * @var array
   */
  protected $inputNames = array();
  
  /**
   * @var array
   */
  protected $validators = array();
  
  /**
   * @var array
   */
  protected $errors = array();
  
  /**
   * @param string $nameSpace
   *
   * @return self
   */
  public function setNameSpace($nameSpace)
  {
    $this->nameSpace = $nameSpace;
    
    return $this;
  }
  
  public function getNameSpace()
  {
    return $this->nameSpace;
  }
  
  /**
   * @param array $names
   *
   * @return self
   */
  public function setDisplayNames(array $displayNames)
  {
    $this->displayNames = $displayNames;
    
    return $this;
  }
  
  /**
   * @param string $inputName
   *
   * @return string
   */
  public function getDisplayName($inputName)
  {
    if (isset($this->displayNames[$inputName])) {
      return $this->displayNames[$inputName];
    } else {
      return $inputName;
    }
  }
  
  public function n($inputName)
  {
    return $this->getDisplayName($inputName);
  }
  
  /**
   * @param array $inputNames
   *
   * return self
   */
  public function setInputNames(array $inputNames)
  {
    $this->inputNames = $inputNames;
    
    return $this;
  }
  
  /**
   * @return array
   */
  public function getInputNames()
  {
    return $this->inputNames;
  }
  
  /**
   * @param array $errors
   *
   * @return void
   */
  public function setErrors($errors)
  {
    $this->errors = $errors;
  }
  
  /**
   * @return array
   */
  public function getErrors()
  {
    return $this->errors;
  }
  
  /**
   * @return boolean
   */
  public function hasError()
  {
    return !empty($this->errors);
  }
  
  public function text($name, $attrs = "")
  {
    return $this->getHtmlWriter($name, $this->createInputName($name), $attrs)->text();
  }
  
  public function password($name, $attrs = "")
  {
    return $this->getHtmlWriter($name, $this->createInputName($name), $attrs)->password();
  }
  
  public function textarea($name, $attrs = "")
  {
    return $this->getHtmlWriter($name, $this->createInputName($name), $attrs)->textarea();
  }
  
  public function hidden($name, $attrs = "")
  {
    return $this->getHtmlWriter($name, $this->createInputName($name), $attrs)->hidden();
  }
  
  public function select($name, $values, $attrs = "")
  {
    if (is_string($values) && strpos($values, ":") !== false) {
      list ($from, $to) = explode(":", $values);
      if (is_number($from) && is_number($to)) {
        $buf = array();
        for ($i = $from; $i <= $to; $i++) {
          $buf[$i] = $i;
        }
        
        $values = $buf;
      } else {
        trigger_error("argument is not a number: {$values}", E_USER_WARNING);
      }
    }
    
    return $this->getHtmlWriter($name, $this->createInputName($name), $attrs)->select($values);
  }
  
  public function radio($name, $values, $attrs = "")
  {
    return $this->getHtmlWriter($name, $this->createInputName($name), $attrs)->radio($values);
  }
  
  public function checkbox($name, $values, $attrs = "")
  {
    return $this->getHtmlWriter($name, $this->createInputName($name), $attrs)->checkbox($values);
  }
  
  public function datetime($name, $yearRange = null, $withSecond = false, $includeBlank = false)
  {
    $writer = $this->getHtmlWriter($name, $this->createInputName("_datetime") . "[{$name}]");
    return $writer->datetime($yearRange, $withSecond, $includeBlank);
  }
  
  public function date($name, $yearRange = null, $includeBlank = false)
  {
    $writer = $this->getHtmlWriter($name, $this->createInputName("_date") . "[{$name}]");
    return $writer->date($yearRange, $includeBlank);
  }
  
  public function file($name, $attrs = "")
  {
    return $this->getHtmlWriter($name, $this->createInputName($name), $attrs)->file();
  }
  
  public function submit(array $values, array $inputNames = array())
  {
    if (empty($values)) {
      return $this;
    }
    
    if (empty($inputNames)) {
      $inputNames = $this->inputNames;
    } else {
      $this->inputNames = $inputNames;
    }
    
    foreach ($values as $inputName => $value) {
      if ($inputName === "_datetime" || $inputName === "_date") {
        list ($k, ) = each($value);
        if (!in_array($k, $inputNames, true)) {
          continue;
        } elseif ($inputName === "_datetime") {
          foreach ($value as $key => $date) {
            if (!isset($date["s"])) {
              $date["s"] = "00";
            }
            
            if ($this->isValidDateValue($date, true)) {
              $this->set(
                $key,
                $date["y"] . "-" .
                $date["m"] . "-" .
                $date["d"] . " " .
                $date["h"] . ":" .
                $date["i"] . ":" .
                $date["s"]
              );
            } else {
              $this->set($key, null);
            }
          }
        } elseif ($inputName === "_date") {
          foreach ($value as $key => $date) {
            if ($this->isValidDateValue($date)) {
              $this->set($key, "{$date['y']}-{$date['m']}-{$date['d']}");
            } else {
              $this->set($key, null);
            }
          }
        }
      } elseif (!in_array($inputName, $inputNames, true)) {
        continue;
      } else {
        $this->set($inputName, $value);
      }
    }
    
    return $this;
  }
  
  /**
   * @return boolean
   */
  public function validate(Sabel_Validator $validator = null)
  {
    if ($validator === null) {
      $validator = $this->buildValidator();
    }
    
    $validator->validate($this->values);
    $this->errors = $validator->getErrors();
    
    return empty($this->errors);
  }
  
  public function toHidden($values = null, $var = null)
  {
    $html = array();
    
    if ($values === null) {
      $values = $this->values;
    }
    
    foreach ($values as $k => $v) {
      if ($var !== null) {
        $k = "{$var}[{$k}]";
      }
      
      if (is_array($v)) {
        $html[] = $this->toHidden($v, $k);
      } else {
        $html[] = '<input type="hidden" name="' . htmlescape($k, APP_ENCODING)
                . '" value="' . htmlescape($v, APP_ENCODING) . '" />';
      }
    }
    
    return implode(PHP_EOL, $html);
  }
  
  protected function createValidator()
  {
    $validator = new Validator();
    $validator->register($this);
    $validator->setDisplayNames($this->displayNames);
    
    return $validator;
  }
  
  protected function buildValidator()
  {
    $validator = $this->createValidator();
    $this->setupValidator($validator);
    
    return $validator;
  }
  
  protected function isValidDateValue($values, $isDatetime = false)
  {
    $keys = array("y", "m", "d");
    
    if ($isDatetime) {
      $keys = array_merge($keys, array("h", "i", "s"));
    }
    
    foreach ($keys as $key) {
      if (!isset($values[$key]) || $values[$key] === "") {
        return false;
      }
    }
    
    return true;
  }
  
  protected function getHtmlWriter($name, $inputName, $attrs = "")
  {
    static $htmlWriter = null;
    
    if ($htmlWriter === null) {
      $htmlWriter = new Form_Html();
    } else {
      $htmlWriter->clear();
    }
    
    $value = $this->get($name);
    
    if (is_string($value)) {
      $value = htmlescape($value, APP_ENCODING);
    }
    
    return $htmlWriter->setName($inputName)->setValue($value)->setAttributes($attrs);
  }
  
  protected function createInputName($inputName)
  {
    if (is_empty($this->nameSpace)) {
      return $inputName;
    } else {
      return $this->nameSpace . "[{$inputName}]";
    }
  }
  
  protected function setupValidator(Sabel_Validator $validator)
  {
    $keys = array();
    
    $validators = $this->validators;
    foreach ($this->inputNames as $inputName) {
      $keys[$inputName] = true;
      if (!isset($validators[$inputName])) continue;
      
      $validator->add($inputName, $validators[$inputName]);
      
      unset($validators[$inputName]);
    }
    
    if ($validators) {
      foreach ($validators as $inputName => $v) {
        if (strpos($inputName, ",") === false) continue;
        
        $comp = true;
        foreach (explode(",", $inputName) as $_inputName) {
          if (!isset($keys[$_inputName])) {
            $comp = false;
            break;
          }
        }
        
        if ($comp) {
          $validator->add($inputName, $v);
        }
      }
    }
  }
}
