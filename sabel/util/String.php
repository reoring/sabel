<?php

/**
 * Sabel_Util_String
 *
 * @category   Util
 * @package    org.sabel.util
 * @author     Ebine Yutaka <ebine.yutaka@sabel.jp>
 * @author     Hamanaka Kazuhiro <hamanaka.kazuhiro@sabel.jp>
 * @copyright  2004-2008 Mori Reo <mori.reo@sabel.jp>
 * @license    http://www.opensource.org/licenses/bsd-license.php  BSD License
 */
class Sabel_Util_String extends Sabel_Object
{
  protected static $isMbstringLoaded = null;
  
  /**
   * @var string
   */
  protected $string = "";
  
  public function __construct($string = "")
  {
    if (self::$isMbstringLoaded === null) {
      self::$isMbstringLoaded = extension_loaded("mbstring");
    }
    
    $this->set($string);
  }
  
  public function set($string)
  {
    if (is_string($string)) {
      $this->string = $string;
    } elseif ($string instanceof self) {
      $this->string = $string->toString();
    } elseif ($string === null) {
      $this->string = "";
    } else {
      $message = __METHOD__ . "() argument must be a string.";
      throw new Sabel_Exception_InvalidArgument($message);
    }
  }
  
  public function __toString()
  {
    return $this->string;
  }
  
  public function length()
  {
    return (self::$isMbstringLoaded) ? mb_strlen($this->string) : strlen($this->string);
  }
  
  public function isEmpty()
  {
    return ($this->string === "");
  }
  
  public function equals($value)
  {
    $args = func_get_args();
    if (empty($args)) return false;
    
    foreach ($args as $string) {
      if ($string instanceof self) {
        $string = $string->toString();
      }
      
      if ($this->string === $string) {
        return true;
      }
    }
    
    return false;
  }
  
  public function append($string)
  {
    $this->string .= $string;
    
    return $this;
  }
  
  public function charAt($pos)
  {
    if ($pos >= $this->length() || $pos < 0) {
      return new self("");
    } elseif (self::$isMbstringLoaded) {
      return new self(mb_substr($this->string, $pos, 1));
    } else {
      return new self($this->string{$pos});
    }
  }
  
  public function last()
  {
    return $this->charAt($this->length() - 1);
  }
  
  public function indexOf($needle, $offset = 0)
  {
    if (self::$isMbstringLoaded) {
      return mb_strpos($this->string, $needle, $offset);
    } else {
      return strpos($this->string, $needle, $offset);
    }
  }
  
  public function trim($charlist = null)
  {
    if (self::$isMbstringLoaded) {
      $this->mbTrim($charlist, "BOTH");
    } else {
      $this->doTrim("trim", $charlist);
    }
    
    return $this;
  }
  
  public function rtrim($charlist = null)
  {
    if (self::$isMbstringLoaded) {
      $this->mbTrim($charlist, "RIGHT");
    } else {
      $this->doTrim("rtrim", $charlist);
    }
    
    return $this;
  }
  
  public function ltrim($charlist = null)
  {
    if (self::$isMbstringLoaded) {
      $this->mbTrim($charlist, "LEFT");
    } else {
      $this->doTrim("ltrim", $charlist);
    }
    
    return $this;
  }
  
  public function pad($padchar, $padlen, $type = STR_PAD_LEFT)
  {
    if (self::$isMbstringLoaded) {
      $filllen = $padlen - $this->length();
      if ($filllen < 1) {
        return $this;
      } else {
        $padChar = new self($padchar);
        if ($type === STR_PAD_LEFT || $type === STR_PAD_RIGHT) {
          if (($_len = $padChar->length()) === 1) {
            $padChar->repeat($filllen);
          } elseif ($_len !== $filllen) {
            if ($filllen < $_len) {
              $padChar->set($padChar->substring(0, $filllen));
            } else {
              $padChar->repeat(floor($filllen / $_len));
              if (($rem = $filllen % $_len) !== 0) {
                $padChar->append($padChar->substring(0, $rem));
              }
            }
          }
          
          if ($type === STR_PAD_LEFT) {
            $this->string = $padChar . $this->string;
          } else {
            $this->string .= $padChar;
          }
        } elseif ($type === STR_PAD_BOTH) {
          $this->pad($padchar, (int)floor($filllen / 2) + $this->length(), STR_PAD_LEFT);
          $this->pad($padchar, $padlen, STR_PAD_RIGHT);
        } else {
          $message = __METHOD__ . "() invalid pad type. Use STR_PAD_LEFT or STR_PAD_RIGHT, STR_PAD_BOTH.";
          throw new Sabel_Exception_InvalidArgument($message);
        }
      }
    } else {
      $this->string = str_pad($this->string, $padlen, $padchar, $type);
    }
    
    return $this;
  }
  
  public function repeat($multiplier)
  {
    $this->string = str_repeat($this->string, $multiplier);
    
    return $this;
  }
  
  public function toUpperCase()
  {
    if (!$this->isEmpty()) {
      $this->string = strtoupper($this->string);
    }
    
    return $this;
  }
  
  public function toLowerCase()
  {
    if (!$this->isEmpty()) {
      $this->string = strtolower($this->string);
    }
    
    return $this;
  }
  
  public function ucfirst()
  {
    if (!$this->isEmpty()) {
      $this->string = ucfirst($this->string);
    }
    
    return $this;
  }
  
  public function lcfirst()
  {
    if (!$this->isEmpty()) {
      $this->string = lcfirst($this->string);
    }
    
    return $this;
  }
  
  public function insert($offset, $string)
  {
    $tmp  = $this->substring(0, $offset);
    $tmp .= $string . $this->substring($offset);
    
    $this->string = $tmp;
    
    return $this;
  }
  
  public function replace($search, $replace)
  {
    $this->string = str_replace($search, $replace, $this->string);
    
    return $this;
  }
  
  public function substring($start, $length = null)
  {
    $func = (self::$isMbstringLoaded) ? "mb_substr" : "substr";
    
    if ($length === null) {
      $string = $func($this->string, $start);
    } else {
      $string = $func($this->string, $start, $length);
    }
    
    if (!$string) {
      $string = "";
    }
    
    return new self($string);
  }
  
  public function substr($start, $length = null)
  {
    return $this->substring($start, $length);
  }
  
  public function explode($separator, $limit = null)
  {
    if ($limit === null) {
      return explode($separator, $this->string);
    } else {
      return explode($separator, $this->string, $limit);
    }
  }
  
  public function split($length = 1)
  {
    if (self::$isMbstringLoaded) {
      $ret = array();
      $i = 0;
      while (true) {
        $str = $this->substring($i, $length);
        if ($str->isEmpty()) {
          break;
        } else {
          $ret[] = $str->toString();
        }
        
        $i += $length;
      }
      
      return $ret;
    } else {
      return str_split($this->string, $length);
    }
  }
  
  public function sha1()
  {
    $this->string = sha1($this->string);
    
    return $this;
  }
  
  public function md5()
  {
    $this->string = md5($this->string);
    
    return $this;
  }
  
  public function cloning()
  {
    return clone $this;
  }
  
  /**
   * @todo implement boundary value
   */
  public function succ()
  {
    $string = $this->string;
    
    for ($i = $this->length(); $i > 0; $i--) {
      $p = $i - 1;
      if ($string{$p} === "9") {
        $string{$p} = "0";
        $str        = "1";
      } elseif ($string{$p} === "z") {
        $string{$p} = "a";
        $str        = "a";
      } elseif ($string{$p} === "Z") {
        $string{$p} = "A";
        $str        = "A";
      } elseif (preg_match("/[^a-zA-Z0-9]/", $string{$p}) === 1) {
        break;
      } else {
        $string{$p} = chr(ord($string{$p}) + 1);
        break;
      }
      
      if ($p === 0) {
        $string = $str . $string;
      } elseif (preg_match("/[^a-zA-Z0-9]/", $string[$p - 1])) {
        $string = substr($string, 0, $p) . $str . substr($string, $p);
        break;
      }
    }
    
    $this->string = $string;
    
    return $this;
  }
  
  public function textlink($htmlescaped = true)
  {
    if ($htmlescaped) {
      $lt = "&lt;";
      $gt = "&gt;";
      $qt = "&quot;";
    } else {
      $lt = '<';
      $gt = '>';
      $qt = '"';
    }
    
    $text = $this->string;
    $chars = '0-9a-zA-Z_\-\.\~\/\?@%=:;&#';
    
    preg_match_all(
      '!' . $lt . 'a .*href=' . $qt . '(https?://[' . $chars . ']+)' .
      $qt . '.*' . $gt . '(.+)' . $lt . '/a' . $gt . '!U',
      $text,
      $matches
    );
    
    $replaces = array();
    if (isset($matches[0])) {
      for ($i = 0, $mc = count($matches[0]); $i < $mc; $i++) {
        $hash = md5hash();
        $replaces[$hash] = '<a href="' . $matches[1][$i] . '">' . $matches[2][$i] . '</a>';
        $text = str_replace($matches[0][$i], $hash, $text);
      }
    }
    
    $text = preg_replace('!https?://[' . $chars . ']+!', '<a href="$0">$0</a>', $text);
    
    if ($replaces) {
      $text = strtr($text, $replaces);
    }
    
    $this->string = $text;
    
    return $this;
  }
  
  protected function mbTrim($charlist, $type = "both")
  {
    if (defined("APP_ENCODING")) {
      $encoding = strtolower(APP_ENCODING);
    } else {
      $encoding = strtolower(mb_internal_encoding());
    }
    
    if ($encoding === "utf-8") {
      $del   = "~";
      $clist = ($charlist === null) ? '[\s　]*' : "[" . str_replace($del, "\\{$del}", $charlist) . "]*";
      
      switch (strtolower($type)) {
        case "both":
          $regex = "^{$clist}(.*?){$clist}";
          break;
        
        case "right":
          $regex = "(.*?){$clist}";
          break;
        
        case "left":
          $regex = "^{$clist}(.*)";
          break;
        
        default:
          $message = __METHOD__ . "() invalid trim type.";
          throw new Sabel_Exception_InvalidArgument($message);
      }
      
      $this->string = preg_replace($del . $regex . "\${$del}us", '$1', $this->string);
    } else {
      if (in_array($encoding, array("euc-jp", "eucjp-win", "eucjp-ms", "cp51932"))) {
        $space = "\xa1\xa1";
      } else {  // sjis etc.
        $space = "\x81\x40";
      }
      
      $clist = ($charlist === null) ? "[\s{$space}]*" : "[{$charlist}]*";
      switch (strtolower($type)) {
        case "both":
          $regex = "^{$clist}(.*?){$clist}$";
          break;
        
        case "right":
          $regex = "(.*?){$clist}$";
          break;
        
        case "left":
          $regex = "^{$clist}(.*)$";
          break;
        
        default:
          $message = __METHOD__ . "() invalid trim type.";
          throw new Sabel_Exception_InvalidArgument($message);
      }
      
      $this->string = mb_ereg_replace($regex, '\\1', $this->string);
    }
  }
  
  protected function doTrim($func, $charlist)
  {
    if ($charlist === null) {
      $this->string = $func($this->string);
    } else {
      $this->string = $func($this->string, $charlist);
    }
  }
}
