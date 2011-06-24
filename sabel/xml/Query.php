<?php

/**
 * Sabel_Xml_Query
 *
 * @category   XML
 * @package    org.sabel.xml
 * @author     Ebine Yutaka <ebine.yutaka@sabel.jp>
 * @copyright  2004-2008 Mori Reo <mori.reo@sabel.jp>
 * @license    http://www.opensource.org/licenses/bsd-license.php  BSD License
 */
class Sabel_Xml_Query
{
  public static function toXpath($query)
  {
    $parts = self::splitByWhiteSpace($query);
    $xpath = "";
    
    $i = 0;
    while (true) {
      if (!isset($parts[$i])) break;
      
      $lowered = strtolower($parts[$i]);
      if (in_array($lowered, array("and", "or"), true)) {
        $xpath .= " {$lowered} ";
        $i++;
      } elseif ($lowered === "not") {
        $xpath .= self::createPartOfXpath($parts[$i + 1], $parts[$i + 2], $parts[$i + 3], true);
        $i += 4;
      } else {
        $path  = $parts[$i];
        $exp   = strtoupper($parts[$i + 1]);
        $value = $parts[$i + 2];
        
        if ($exp === "IS" && $value === "NOT") {
          $xpath .= self::createPartOfXpath($path, "IS", "NOT NULL");
          $i += 4;
        } else {
          $xpath .= self::createPartOfXpath($path, $exp, $value);
          $i += 3;
        }
      }
    }
    
    return $xpath;
  }
  
  protected static function createPartOfXpath($path, $exp, $value, $not = false)
  {
    $path  = str_replace(".", "/", $path);
    $exp   = strtoupper($exp);
    $hasAt = false;
    
    if ($path{0} === "@") {
      $path = "." . $path;
    }
    
    if (strpos($path, "@") !== false) {
      $path  = str_replace("@", "/@", $path);
      $hasAt = true;
    }
    
    if ($exp === "IS") {
      if (!$hasAt) {
        $path = ".//" . $path;
      }
      
      if ($value === "NULL") {
        return "not({$path})";
      } elseif ($value === "NOT NULL") {
        return $path;
      }
    } elseif ($path === "value()") {
      $path = "./text()";
    } elseif (!$hasAt) {
      $path .= "/text()";
    }
    
    $xpath = "";
    if (in_array($exp, array("=", "!=", ">=", "<="), true)) {
      $xpath = "{$path}{$exp}{$value}";
    } elseif ($exp === "LIKE") {
      $_value = substr($value, 1, -1);
      $first  = $_value{0};
      $last   = $_value{strlen($_value) - 1};
      
      if ($first === "%" && $last === "%") {
        $xpath = "contains({$path}, '" . substr($_value, 1, -1) . "')";
      } elseif ($first === "%" && $last !== "%") {
        $xpath = "ends-with({$path}, '" . substr($_value, 1) . "')";
      } elseif ($first !== "%" && $last === "%") {
        $xpath = "starts-with({$path}, '" . substr($_value, 0, -1) . "')";
      } else {
        $xpath = "contains({$path}, '{$_value}')";
      }
    }
    
    return ($not) ? "not({$xpath})" : $xpath;
  }
  
  protected static function splitByWhiteSpace($query)
  {
    $replace = array();
    if (preg_match_all("/'.+'/U", $query, $matches)) {
      $hash = md5hash();
      foreach ($matches[0] as $i => $match) {
        $replace[$hash . $i] = $match;
        $query = str_replace($match, $hash . $i, $query);
      }
    }
    
    $query = preg_replace("/ {2,}/", " ", $query);
    $parts = explode(" ", $query);
    
    if ($replace) {
      foreach ($parts as &$part) {
        if (isset($replace[$part])) {
          $part = $replace[$part];
        }
      }
    }
    
    return $parts;
  }
}
