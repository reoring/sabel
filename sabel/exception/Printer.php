<?php

/**
 * Sabel_Exception_Printer
 *
 * @category   Exception
 * @package    org.sabel.exception
 * @author     Ebine Yutaka <ebine.yutaka@sabel.jp>
 * @copyright  2004-2008 Mori Reo <mori.reo@sabel.jp>
 * @license    http://www.opensource.org/licenses/bsd-license.php  BSD License
 */
class Sabel_Exception_Printer
{
  public static function printTrace(Exception $exception, $eol = PHP_EOL, $return = false)
  {
    $result = array();
    
    foreach ($exception->getTrace() as $line) {
      $trace = array();
      
      if (isset($line["file"])) {
        $trace[] = "FILE: {$line["file"]}({$line["line"]})";
      } else {
        $trace[] = "FILE: Unknown";
      }
      
      $args = array();
      if (isset($line["args"]) && !empty($line["args"])) {
        foreach ($line["args"] as $arg) {
          if (is_object($arg)) {
            $args[] = "(Object)" . get_class($arg);
          } elseif (is_bool($arg)) {
            $args[] = ($arg) ? "true" : "false";
          } elseif (is_string($arg)) {
            $args[] = '"' . $arg . '"';
          } elseif (is_int($arg) || is_float($arg)) {
            $args[] = $arg;
          } elseif (is_array($arg)) {
            $args[] = self::arrayToString($arg);
          } elseif (is_resource($arg)) {
            $args[] = "(Resource)" . get_resource_type($arg);
          } elseif ($arg === null) {
            $args[] = "null";
          } else {
            $args[] = "(" . ucfirst(gettype($arg)) . ")" . $arg;
          }
        }
      }
      
      $args = implode(", ", $args);
      
      if (isset($line["class"])) {
        $trace[] = "CALL: " . $line["class"]
                 . $line["type"] . $line["function"] . "({$args})";
      } else {
        $trace[] = "FUNCTION: " . $line["function"] . "({$args})";
      }
      
      $result[] = implode($eol, $trace);
    }
    
    $contents = implode($eol . $eol, $result);
    
    if ($return) {
      return $contents;
    } else {
      echo $contents;
    }
  }
  
  protected static function arrayToString($array)
  {
    $ret = array();
    foreach ($array as $k => $v) {
      $k = '"' . $k . '"';
      if (is_object($v)) {
        $ret[] = $k . " => (Object)" . get_class($v);
      } elseif (is_bool($v)) {
        $str = ($v) ? "true" : "false";
        $ret[] = $k . " => " . $str;
      } elseif (is_string($v)) {
        $ret[] = $k . ' => "' . $v . '"';
      } elseif (is_int($v) || is_float($v)) {
        $ret[] = $k . " => " . $v;
      } elseif (is_array($v)) {
        $ret[] = $k . " => array(...)";
      } elseif (is_resource($v)) {
        $ret[] = $k . " => (Resource)" . get_resource_type($v);
      } elseif ($v === null) {
        $ret[] = $k . " => null";
      } else {
        $ret[] = $k . " => (" . ucfirst(gettype($v)) . ")" . $v;
      }
    }
    
    return "array(" . implode(", ", $ret) . ")";
  }
}
