<?php

/**
 * Validator
 *
 * @category   Request
 * @package    lib
 * @author     Ebine Yutaka <ebine.yutaka@sabel.jp>
 * @copyright  2004-2008 Mori Reo <mori.reo@sabel.jp>
 * @license    http://www.opensource.org/licenses/bsd-license.php  BSD License
 */
class Validator extends Sabel_Validator
{
  public function required($name, $value)
  {
    if (is_empty($value)) {
      return $this->getDisplayName($name) . "を入力してください";
    }
  }
  
  public function integer($name, $value)
  {
    if (!is_empty($value) && !is_number($value)) {
      return $this->getDisplayName($name) . "は整数で入力してください";
    }
  }
  
  public function numeric($name, $value)
  {
    if (!is_empty($value) && !is_numeric($value)) {
      return $this->getDisplayName($name) . "は数値で入力してください";
    }
  }
  
  public function naturalNumber($name, $value)
  {
    if (!is_empty($value) && !is_natural_number($value)) {
      return $this->getDisplayName($name) . "は整数で入力してください";
    }
  }
  
  public function alnum($name, $value)
  {
    if (!is_empty($value) && preg_match('/^[0-9a-zA-Z]+$/', $value) === 0) {
      return $this->getDisplayName($name) . "は半角英数字で入力してください";
    }
  }
  
  public function strlen($name, $value, $max)
  {
    if (!is_empty($value) && mb_strlen($value) > $max) {
      return $this->getDisplayName($name) . "は{$max}文字以内で入力してください";
    }
  }
  
  public function strwidth($name, $value, $max)
  {
    if (!is_empty($value) && mb_strwidth($value) > $max) {
      return $this->getDisplayName($name) . "は全角" . floor($max / 2) . "文字以内で入力してください";
    }
  }
  
  public function max($name, $value, $max)
  {
    if (!is_empty($value) && is_number($value) && $value > $max) {
      return $this->getDisplayName($name) . "は{$max}以下で入力してください";
    }
  }
  
  public function min($name, $value, $min)
  {
    if (!is_empty($value) && is_number($value) && $value < $min) {
      return $this->getDisplayName($name) . "は{$min}以上で入力してください";
    }
  }
  
  public function boolean($name, $value)
  {
    if (!is_empty($value) && !in_array($value, array("0", "1", false, true, 0, 1), true)) {
      return $this->getDisplayName($name) . "の形式が不正です";
    }
  }
  
  public function date($name, $value)
  {
    if (!is_empty($value)) {
      @list ($y, $m, $d) = explode("-", str_replace("/", "-", $value));
      if (!checkdate($m, $d, $y)) {
        return $this->getDisplayName($name) . "の形式が不正、または無効な日付です";
      }
    }
  }
  
  public function datetime($name, $value)
  {
    if (!is_empty($value)) {
      @list ($date, $time) = explode(" ", str_replace("/", "-", $value));
      @list ($y, $m, $d) = explode("-", $date);
      
      if (!checkdate($m, $d, $y)) {
        return $this->getDisplayName($name) . "の形式が不正、または無効な日付です";
      } else {
        if (preg_match('/^((0?|1)[\d]|2[0-3]):(0?[\d]|[1-5][\d]):(0?[\d]|[1-5][\d])$/', $time) === 0) {
          return $this->getDisplayName($name) . "の形式が不正、または無効な日付です";
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
        return $this->getDisplayName($name) . "の形式が不正です";
      } elseif ($size !== null) {
        if (strlen($data) > strtoint($size)) {
          return $this->getDisplayName($name) . "のサイズが{$size}Bを超えています";
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
        return implode("と", $ns) . "が一致しません";
      }
    }
  }
  
  public function nnumber($name, $value)
  {
    return $this->naturalNumber($name, $value);
  }
}
