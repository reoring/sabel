<?php

/**
 * Sabel
 *
 * @category   Core
 * @package    org.sabel.core
 * @author     Mori Reo <mori.reo@sabel.jp>
 * @author     Ebine Yutaka <yutaka@ebine.org>
 * @copyright  2004-2008 Mori Reo <mori.reo@sabel.jp>
 * @license    http://www.opensource.org/licenses/bsd-license.php  BSD License
 */
final class Sabel
{
  /**
   * @var array
   */
  private static $readableFiles = array();
  
  /**
   * @var int
   */
  private static $readableFilesNum = 0;
  
  /**
   * @var array
   */
  private static $required = array();
  
  /**
   * @var array
   */
  private static $fileUsing = array();
  
  public static function getPath()
  {
    return dirname(__FILE__);
  }
  
  public static function using($className)
  {
    if (class_exists($className, false)) {
      return true;
    } else {
      return self::autoload($className);
    }
  }
  
  public static function autoload($className)
  {
    if (isset(self::$required[$className])) return true;
    
    if (isset(self::$readableFiles[$className])) {
      require (self::$readableFiles[$className]);
      self::$required[$className] = 1;
      return true;
    } elseif ($path = self::getFilePath($className)) {
      require ($path);
      
      self::$required[$className] = 1;
      self::$readableFiles[$className] = $path;
      return true;
    } else {
      return false;
    }
  }
  
  public static function fileUsing($path, $once = false)
  {
    if ($once && isset(self::$fileUsing[$path])) return true;
    
    if (isset(self::$readableFiles[$path])) {
      $readable = true;
    } elseif (is_readable($path)) {
      $readable = true;
      self::$readableFiles[$path] = $path;
    } else {
      $readable = false;
    }
    
    if ($readable) {
      ($once) ? require_once ($path) : require ($path);
      self::$fileUsing[$path] = 1;
      return true;
    }
    
    return false;
  }
  
  private static function getFilePath($className)
  {
    static $includePath = null;
    static $paths = null;
    
    $exp = explode("_", $className);
    
    if (count($exp) === 1) {
      $path = $exp[0] . ".php";
    } else {
      $class = array_pop($exp);
      $prePath = implode("/", array_map("lcfirst", $exp));
      $path = $prePath . DIRECTORY_SEPARATOR . $class . ".php";
    }
    
    if ($includePath === null) {
      $includePath = get_include_path();
    } elseif (($incPath = get_include_path()) !== $includePath) {
      $includePath = $incPath;
      $paths = null;
    }
    
    if ($paths === null) {
      $paths = explode(PATH_SEPARATOR, $includePath);
    }
    
    foreach ($paths as $p) {
      $fullPath = $p . DIRECTORY_SEPARATOR . $path;
      if (is_readable($fullPath)) return $fullPath;
    }
    
    return false;
  }
  
  public static function main()
  {
    $SABEL = "sabel" . DIRECTORY_SEPARATOR;
    
    require ($SABEL . "Object.php");
    require ($SABEL . "Logger.php");
    require ($SABEL . "Bus.php");
    require ($SABEL . "Config.php");
    require ($SABEL . "Context.php");
    require ($SABEL . "Request.php");
    require ($SABEL . "Session.php");
    require ($SABEL . "Response.php");
    require ($SABEL . "View.php");
    
    require ($SABEL . "functions" . DIRECTORY_SEPARATOR . "core.php");
    require ($SABEL . "functions" . DIRECTORY_SEPARATOR . "db.php");
    
    $BUS     = $SABEL . "bus"        . DIRECTORY_SEPARATOR;
    $MAP     = $SABEL . "map"        . DIRECTORY_SEPARATOR;
    $CTRL    = $SABEL . "controller" . DIRECTORY_SEPARATOR;
    $RESP    = $SABEL . "response"   . DIRECTORY_SEPARATOR;
    $SESSION = $SABEL . "session"    . DIRECTORY_SEPARATOR;
    $VIEW    = $SABEL . "view"       . DIRECTORY_SEPARATOR;
    $UTIL    = $SABEL . "util"       . DIRECTORY_SEPARATOR;
    
    require ($BUS  . "Config.php");
    require ($BUS  . "Processor.php");
    
    require ($MAP . "Configurator.php");
    require ($MAP . "Candidate.php");
    require ($MAP . "Destination.php");
    require ($MAP . "config" . DIRECTORY_SEPARATOR . "Route.php");
    
    require ($RESP . "Object.php");
    require ($RESP . "Status.php");
    require ($RESP . "Redirector.php");
    require ($RESP . "Header.php");
    
    require ($SESSION . "Abstract.php");
    require ($SESSION . "PHP.php");
    
    require ($VIEW . "Renderer.php");
    require ($VIEW . "Object.php");
    require ($VIEW . "Location.php");
    require ($VIEW . "location" . DIRECTORY_SEPARATOR . "File.php");
    
    require ($UTIL . "HashList.php");
    require ($UTIL . "VariableCache.php");
    
    require ($SABEL . "request"    . DIRECTORY_SEPARATOR . "Object.php");
    require ($SABEL . "controller" . DIRECTORY_SEPARATOR . "Page.php");
    require ($SABEL . "exception"  . DIRECTORY_SEPARATOR . "Runtime.php");
    require ($SABEL . "logger"     . DIRECTORY_SEPARATOR . "Interface.php");
  }
  
  public static function init()
  {
    $path  = "sabel" . DIRECTORY_SEPARATOR . "Sabel";
    $cache = Sabel_Util_VariableCache::create($path);
    
    if ($files = $cache->read("readableFiles")) {
      self::$readableFiles = $files;
      self::$readableFilesNum = count($files);
    }
  }
  
  public static function shutdown()
  {
    if (self::$readableFilesNum !== count(self::$readableFiles)) {
      $path  = "sabel" . DIRECTORY_SEPARATOR . "Sabel";
      $cache = Sabel_Util_VariableCache::create($path);
      $cache->write("readableFiles", self::$readableFiles);
      $cache->save();
    }
  }
}

/* register autoload method */
spl_autoload_register(array("Sabel", "autoload"));

Sabel::main();
