<?php

/**
 * Sabel_I18n_Gettext
 *
 * @category   I18n
 * @package    org.sabel.i18n
 * @author     Ebine Yutaka <ebine.yutaka@sabel.jp>
 * @copyright  2004-2008 Mori Reo <mori.reo@sabel.jp>
 * @license    http://www.opensource.org/licenses/bsd-license.php  BSD License
 */
class Sabel_I18n_Gettext
{
  protected static $instance = null;
  
  protected $fileName    = "messages.php";
  protected $localesDir  = "";
  protected $codeset     = array();
  protected $initialized = false;
  
  private function __construct()
  {
    if (defined("LOCALE_DIR_PATH")) {
      $this->localesDir = LOCALE_DIR_PATH;
    } else {
      $this->localesDir = RUN_BASE . DS . "locale";
    }
  }
  
  public static function getInstance()
  {
    if (self::$instance === null) {
      self::$instance = new self();
    }
    
    return self::$instance;
  }
  
  public function isInitialized()
  {
    return $this->initialized;
  }
  
  public function setMessagesFileName($fileName)
  {
    $this->fileName = $fileName;
    
    if ($this->initialized) {
      Sabel_I18n_Sabel_Gettext::setMessagesFileName($this->fileName);
    }
    
    return $this;
  }
  
  public function setLocalesDir($path)
  {
    $this->localesDir = $path;
    
    if ($this->initialized) {
      Sabel_I18n_Sabel_Gettext::setLocalesDir($path);
    }
    
    return $this;
  }
  
  public function setCodeset($codeset, $fileName = "")
  {
    if ($fileName === "") {
      $fileName = $this->fileName;
    }
    
    $this->codeset[$fileName] = $codeset;
    
    if ($this->initialized) {
      Sabel_I18n_Sabel_Gettext::setCodeset($fileName, $codeset);
    }
    
    return $this;
  }
  
  public function init($acceptLanguage = null)
  {
    if ($this->initialized) return;
    
    if ($languages = $this->getAcceptLanguages($acceptLanguage)) {
      $dirs   = $this->getLocaleDirs();
      $locale = null;
      foreach ($languages as $language) {
        if (strpos($language, "-") !== false) {
          list ($ll, $cc) = explode("-", $language);
          $language = $ll . "_" . strtoupper($cc);
        } else {
          $ll = "";
        }
        
        if (isset($dirs[$language])) {
          $locale = $language;
          break;
        } elseif (isset($dirs[$ll])) {
          $locale = $ll;
          break;
        }
      }
      
      if (isset($this->codeset[$this->fileName])) {
        $codeset = $this->codeset[$this->fileName];
      } else {
        $codeset = null;
      }
      
      Sabel_I18n_Sabel_Gettext::initialize($this->fileName, $this->localesDir, $codeset, $locale);
    }
    
    $this->initialized = true;
  }
  
  protected function getAcceptLanguages($acceptLanguage)
  {
    $languages = array();
    
    if ($acceptLanguage === null) {
      if (isset($_SERVER["HTTP_ACCEPT_LANGUAGE"])) {
        $acceptLanguage = $_SERVER["HTTP_ACCEPT_LANGUAGE"];
      } else {
        return $languages;
      }
    }
    
    if (!empty($acceptLanguage)) {
      foreach (explode(",", $acceptLanguage) as $lang) {
        if (strpos($lang, ";") === false) {
          $q = "1.0";
        } else {
          list ($lang, $q) = explode(";", $lang);
          $q = str_replace("q=", "", $q);
        }
        
        $languages[$q] = $lang;
      }
      
      krsort($languages, SORT_NUMERIC);
      $languages = array_values($languages);
    }
    
    return $languages;
  }
  
  private function getLocaleDirs()
  {
    if ((ENVIRONMENT & PRODUCTION) > 0) {
      $cache = CACHE_DIR_PATH . DS . "sabel" . DS . "locale_dirs.php";
      
      if (is_file($cache)) {
        include ($cache);
        $dirs = $locales;
      } else {
        $dirs = $this->_getLocaleDirs();
        $code = array("<?php" . PHP_EOL . PHP_EOL . '$locales = array(');
        foreach (array_keys($dirs) as $dir) {
          $code[] = '"' . $dir . '" => 1,';
        }
        
        file_put_contents($cache, implode("", $code) . ");");
      }
    } else {
      $dirs = $this->_getLocaleDirs();
    }
    
    return $dirs;
  }
  
  private function _getLocaleDirs()
  {
    $dir  = $this->localesDir . DS;
    $dirs = array();
    
    foreach (scandir($dir) as $item) {
      if ($item === "." || $item === "..") continue;
      if (is_dir($dir . $item)) $dirs[$item] = 1;
    }
    
    return $dirs;
  }
}
