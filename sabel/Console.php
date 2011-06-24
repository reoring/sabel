<?php

/**
 * Sabel_Console
 *
 * @category   Sakle
 * @package    org.sabel.sakle
 * @author     Ebine Yutaka <ebine.yutaka@sabel.jp>
 * @copyright  2004-2008 Mori Reo <mori.reo@sabel.jp>
 * @license    http://www.opensource.org/licenses/bsd-license.php  BSD License
 */
class Sabel_Console
{
  const MSG_INFO = 0x01;
  const MSG_WARN = 0x02;
  const MSG_MSG  = 0x04;
  const MSG_ERR  = 0x08;
  
  private $stdin = null;
  private $ends = array("exit", "quit", "\q");
  
  private static $headers = array(self::MSG_INFO => "[\x1b[1;32m%s\x1b[m]",
                                  self::MSG_WARN => "[\x1b[1;35m%s\x1b[m]",
                                  self::MSG_MSG  => "[\x1b[1;34m%s\x1b[m]",
                                  self::MSG_ERR  => "[\x1b[1;31m%s\x1b[m]");
  
  private static $winHeaders = array(self::MSG_INFO => "[%s]",
                                     self::MSG_WARN => "[%s]",
                                     self::MSG_MSG  => "[%s]",
                                     self::MSG_ERR  => "[%s]");
  
  public static function success($msg)
  {
    echo self::getHeader(self::MSG_INFO, "SUCCESS") . " $msg" . PHP_EOL;
  }
  
  public static function warning($msg)
  {
    echo self::getHeader(self::MSG_WARN, "WARNING") . " $msg" . PHP_EOL;
  }
  
  public static function message($msg)
  {
    echo self::getHeader(self::MSG_MSG, "MESSAGE") . " $msg" . PHP_EOL;
  }
  
  public static function error($msg)
  {
    echo self::getHeader(self::MSG_ERR, "FAILURE") . " $msg" . PHP_EOL;
  }
  
  public static function hasOption($opt, $arguments)
  {
    if (strlen($opt) === 1) {
      foreach ($arguments as $argument) {
        if (preg_match('/^-[a-zA-Z]*' . $opt . '[a-zA-Z]*$/', $argument)) return true;
      }
    } else {
      foreach ($arguments as $argument) {
        if (preg_match("/^--{$opt}(=.*)?$/", $argument)) return true;
      }
    }
    
    return false;
  }
  
  public static function getOption($opt, &$arguments, $unset = true)
  {
    $value = null;
    
    if (strlen($opt) === 1) {
      $index = array_search("-" . $opt, $arguments, true);
      if ($index !== false && isset($arguments[$index + 1])) {
        $value = $arguments[$index + 1];
        if ($unset) {
          unset($arguments[$index]);
          unset($arguments[$index + 1]);
          $arguments = array_values($arguments);
        }
      }
    } else {
      foreach ($arguments as $idx => $argument) {
        if ($argument === "--{$opt}") {
          if ($unset) {
            unset($arguments[$idx]);
            $arguments = array_values($arguments);
          }
          
          break;
        } if (preg_match("/^--{$opt}=(.+)$/", $argument, $matches)) {
          $value = $matches[1];
          if ($unset) {
            unset($arguments[$idx]);
            $arguments = array_values($arguments);
          }
          
          break;
        }
      }
    }
    
    return $value;
  }
  
  public static function getHeader($type, $headMsg)
  {
    if ((isset($_SERVER["IS_WINDOWS"]) && $_SERVER["IS_WINDOWS"]) ||
        DIRECTORY_SEPARATOR === '\\') {
      return sprintf(self::$winHeaders[$type], $headMsg);
    } else {
      return sprintf(self::$headers[$type], $headMsg);
    }
  }
  
  public function __construct($ends = null)
  {
    if ($ends !== null) {
      if (is_array($ends)) {
        $this->ends = $ends;
      } else {
        $message = __METHOD__ . "() argument must be an array.";
        throw new Sabel_Exception_InvalidArgument($message);
      }
    }
    
    $this->stdin = fopen("php://stdin", "r");
  }
  
  public function read($message, $default = null, $trim = true)
  {
    if ($default === null) {
      echo $message . "> ";
    } else {
      echo $message . " [{$default}]> ";
    }
    
    $input = fgets($this->stdin);
    $input = ($trim) ? trim($input) : $input;
    
    if ($input === "" && $default !== null) {
      $input = $default;
    }
    
    if (in_array($input, $this->ends, true)) {
      return false;
    } else {
      return $input;
    }
  }
  
  public function quit()
  {
    if (is_resource($this->stdin)) {
      fclose($this->stdin);
    }
  }
}
