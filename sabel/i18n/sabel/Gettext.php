<?php

/**
 * Sabel_I18n_Sabel_Gettext
 *
 * @category   I18n
 * @package    org.sabel.i18n
 * @author     Ebine Yutaka <ebine.yutaka@sabel.jp>
 * @copyright  2004-2008 Mori Reo <mori.reo@sabel.jp>
 * @license    http://www.opensource.org/licenses/bsd-license.php  BSD License
 */
class Sabel_I18n_Sabel_Gettext
{
  private static $fileName   = "";
  private static $locale     = null;
  private static $localesDir = array();
  private static $codeSet    = array();
  private static $messages   = array();
  
  public static function initialize($fileName, $localesDir, $codeSet, $locale)
  {
    self::$fileName   = $fileName;
    self::$localesDir = $localesDir;
    self::$locale     = $locale;
    self::$codeSet[$fileName] = $codeSet;
  }
  
  public static function _($msgid)
  {
    if (self::$locale === null) {
      return $msgid;
    } else {
      $fileName = self::$fileName;
      $messages = self::getMessages($fileName);
      
      if (isset($messages[$msgid])) {
        $message = ($messages[$msgid] === "") ? $msgid : $messages[$msgid];
      } else {
        $message = $msgid;
      }
      
      if (isset(self::$codeSet[$fileName])) {
        $from = self::getInternalEncoding();
        return mb_convert_encoding($message, self::$codeSet[$fileName], $from);
      } else {
        return $message;
      }
    }
  }
  
  public static function setMessagesFileName($fileName)
  {
    self::$fileName = $fileName;
  }
  
  public static function setLocalesDir($path)
  {
    self::$localesDir = $path;
  }
  
  public static function setCodeset($fileName, $codeSet)
  {
    self::$codeSet[$fileName] = $codeSet;
  }
  
  public static function setLocale($locale)
  {
    self::$locale = $locale;
  }
  
  private static function getMessages($path)
  {
    $locale   = self::$locale;
    $fileName = self::$fileName;
    
    if (isset(self::$messages[$locale][$fileName])) {
      return self::$messages[$locale][$fileName];
    } else {
      $filePath = self::$localesDir . DS . $locale . DS . $fileName;
      
      if (is_readable($filePath)) {
        include ($filePath);
        return self::$messages[$locale][$fileName] = $messages;
      } elseif (strpos($locale, "_") !== false) {
        list ($lang) = explode("_", $locale);
        $filePath = self::$localesDir . DS . $lang . DS . $fileName;
        
        if (is_readable($filePath)) {
          include ($filePath);
          return self::$messages[$locale][$fileName] = $messages;
        }
      }
    }
    
    return self::$messages[$locale][$fileName] = false;
  }
  
  private static function getInternalEncoding()
  {
    static $encoding = null;
    
    if ($encoding === null) {
      return $encoding = ini_get("mbstring.internal_encoding");
    } else {
      return $encoding;
    }
  }
}
