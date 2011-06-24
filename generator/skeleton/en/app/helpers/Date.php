<?php

/**
 * Helpers_Date
 *
 * @category   Helper
 * @package    org.sabel.helper
 * @author     Ebine Yutaka <ebine.yutaka@sabel.jp>
 * @copyright  2004-2008 Mori Reo <mori.reo@sabel.jp>
 * @license    http://www.opensource.org/licenses/bsd-license.php BSD License
 */
class Helpers_Date
{
  const NORMAL  = 0;
  const ATOM    = 1;
  const RSS     = 2;
  const COOKIE  = 3;
  const ISO     = 4;
  const RFC822  = 5;
  const RFC850  = 6;
  const RFC1036 = 7;
  const RFC1123 = 8;
  const RFC2822 = 9;
  const RFC     = 10;
  const W3C     = 11;
  
  private static $formats = array(self::NORMAL  => array(
                                    "all"  => "Y-m-d H:i:s",
                                    "date" => "Y-m-d",
                                    "time" => "H:i:s"),
                                  
                                  self::ATOM    => array(
                                    "all"  => "c",
                                    "date" => "Y-m-d",
                                    "time" => "H:i:sP"),
                                  
                                  self::RSS     => array(
                                    "all"  => "r",
                                    "date" => "D, d M Y",
                                    "time" => "H:i:s O"),
                                  
                                  self::COOKIE  => array(
                                    "all"  => "l, d-M-y H:i:s T",
                                    "date" => "l, d-M-y",
                                    "time" => "H:i:s T"),
                                  
                                  self::ISO     => array(
                                    "all"  => "Y-m-d\TH:i:sO",
                                    "date" => "Y-m-d",
                                    "time" => "H:i:sO"),
                                  
                                  self::RFC822  => array(
                                    "all"  => "D, d M y H:i:s O",
                                    "date" => "D, d M y",
                                    "time" => "H:i:s O"),
                                  
                                  self::RFC850  => array(
                                    "all"  => "l, d-M-y H:i:s T",
                                    "date" => "l, d-M-y",
                                    "time" => "H:i:s T"),
                                  
                                  self::RFC1036 => array(
                                    "all"  => "D, d M y H:i:s O",
                                    "date" => "D, d M y",
                                    "time" => "H:i:s O"),
                                  
                                  self::RFC1123 => array(
                                    "all"  => "r",
                                    "date" => "D, d M Y",
                                    "time" => "H:i:s O"),
                                  
                                  self::RFC2822 => array(
                                    "all"  => "r",
                                    "date" => "D, d M Y",
                                    "time" => "H:i:s O"),
                                  
                                  self::RFC     => array(
                                    "all"  => "r",
                                    "date" => "D, d M Y",
                                    "time" => "H:i:s O"),
                                  
                                  self::W3C     => array(
                                    "all"  => "c",
                                    "date" => "Y-m-d",
                                    "time" => "H:i:sP"));
  
  protected
    $timestamp = null,
    $format    = self::NORMAL;
  
  public static function format($date, $format)
  {
    return date(self::$formats[$format]["all"], strtotime($date));
  }
  
  public function __construct($arg = null)
  {
    if ($arg === null) {
      $this->timestamp = time();
    } elseif (is_string($arg)) {
      $this->timestamp = strtotime($arg);
    } elseif (is_array($arg)) {
      $y = (isset($arg["y"])) ? $arg["y"] : date("Y");
      $m = (isset($arg["m"])) ? $arg["m"] : date("m");
      $d = (isset($arg["d"])) ? $arg["d"] : 1;
      $h = (isset($arg["h"])) ? $arg["h"] : 0;
      $i = (isset($arg["i"])) ? $arg["i"] : 0;
      $s = (isset($arg["s"])) ? $arg["s"] : 0;
      
      $this->timestamp = mktime($h, $i, $s, $m, $d, $y);
    } else {
      throw new Exception("Helpers_Date::__construct() invalid parameter.");
    }
  }
  
  public function __toString()
  {
    return $this->getDateTime();
  }
  
  public function setFormat($format)
  {
    $this->format = $format;
    
    return $this;
  }
  
  public function getDatetime()
  {
    return date(self::$formats[$this->format]["all"], $this->timestamp);
  }
  
  public function getDate()
  {
    return date(self::$formats[$this->format]["date"], $this->timestamp);
  }
  
  public function getTime()
  {
    return date(self::$formats[$this->format]["time"], $this->timestamp);
  }
  
  public function getYear($twoDigits = false)
  {
    if ($twoDigits) {
      return date("y", $this->timestamp);
    } else {
      return date("Y", $this->timestamp);
    }
  }
  
  public function getMonth($withLeadingZeros = true)
  {
    if ($withLeadingZeros) {
      return date("m", $this->timestamp);
    } else {
      return date("n", $this->timestamp);
    }
  }
  
  public function getTextualMonth($short = true)
  {
    if ($short) {
      return date("M", $this->timestamp);
    } else {
      return date("F", $this->timestamp);
    }
  }
  
  public function getDay($withLeadingZeros = true)
  {
    if ($withLeadingZeros) {
      return date("d", $this->timestamp);
    } else {
      return date("j", $this->timestamp);
    }
  }
  
  public function getLastDay()
  {
    $timestamp = mktime($this->getHour(),
                        $this->getMinute(),
                        $this->getSecond(),
                        $this->getMonth() + 1,
                        0,
                        $this->getYear());
    
    return date("d", $timestamp);
  }
  
  public function getHour($withLeadingZeros = true)
  {
    if ($withLeadingZeros) {
      return date("H", $this->timestamp);
    } else {
      return date("G", $this->timestamp);
    }
  }
  
  public function getHourBy12Format($withLeadingZeros = true)
  {
    if ($withLeadingZeros) {
      return date("h", $this->timestamp);
    } else {
      return date("g", $this->timestamp);
    }
  }
  
  public function getMinute()
  {
    return date("i", $this->timestamp);
  }
  
  public function getSecond()
  {
    return date("s", $this->timestamp);
  }
  
  public function getMeridiem($lower = true)
  {
    return date(($lower) ? "a" : "A", $this->timestamp);
  }
  
  public function getTextualWeek($short = true)
  {
    if ($short) {
      return date("D", $this->timestamp);
    } else {
      return date("l", $this->timestamp);
    }
  }
  
  public function getWeekNumber()
  {
    return date("w", $this->timestamp);
  }
  
  public function ymd($sep = "-")
  {
    return $this->getYear() . $sep . $this->getMonth() . $sep . $this->getDay();
  }
  
  public function his($sep = ":")
  {
    return $this->getHour() . $sep . $this->getMinute() . $sep . $this->getSecond();
  }
  
  public function incYear($year = 1)
  {
    $year = $this->getYear() + $year;
    
    $this->timestamp = mktime($this->getHour(),
                              $this->getMinute(),
                              $this->getSecond(),
                              $this->getMonth(),
                              $this->getDay(),
                              $year);
    
    return $year;
  }
  
  public function decYear($year = 1)
  {
    $year = $this->getYear() - $year;
    
    $this->timestamp = mktime($this->getHour(),
                              $this->getMinute(),
                              $this->getSecond(),
                              $this->getMonth(),
                              $this->getDay(),
                              $year);
    
    return $year;
  }
  
  public function incMonth($month = 1)
  {
    $month = $this->getMonth() + $month;
    
    $this->timestamp = mktime($this->getHour(),
                              $this->getMinute(),
                              $this->getSecond(),
                              $month,
                              $this->getDay(),
                              $this->getYear());
    
    return $month;
  }
  
  public function decMonth($month = 1)
  {
    $month = $this->getMonth() - $month;
    
    $this->timestamp = mktime($this->getHour(),
                              $this->getMinute(),
                              $this->getSecond(),
                              $month,
                              $this->getDay(),
                              $this->getYear());
    
    return $month;
  }
  
  public function incDay($day = 1)
  {
    $this->timestamp += 86400 * $day;
    
    return $this->getDay();
  }
  
  public function decDay($day = 1)
  {
    $this->timestamp -= 86400 * $day;
    
    return $this->getDay();
  }
  
  public function incHour($hour = 1)
  {
    $this->timestamp += 3600 * $hour;
    
    return $this->getHour();
  }
  
  public function decHour($hour = 1)
  {
    $this->timestamp -= 3600 * $hour;
    
    return $this->getHour();
  }
  
  public function incMinute($min = 1)
  {
    $this->timestamp += 60 * $min;
    
    return $this->getMinute();
  }
  
  public function decMinute($min = 1)
  {
    $this->timestamp -= 60 * $min;
    
    return $this->getMinute();
  }
  
  public function incSecond($second = 1)
  {
    $this->timestamp += $second;
    
    return $this->getSecond();
  }
  
  public function decSecond($second = 1)
  {
    $this->timestamp -= $second;

    return $this->getSecond();
  }
  
  public function y()
  {
    return $this->getYear();
  }
  
  public function m()
  {
    return $this->getMonth();
  }
  
  public function d()
  {
    return $this->getDay();
  }
  
  public function h()
  {
    return $this->getHour();
  }
  
  public function i()
  {
    return $this->getMinute();
  }
  
  public function s()
  {
    return $this->getSecond();
  }
}
