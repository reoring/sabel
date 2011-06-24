<?php

define("_INT",      Sabel_Db_Type::INT);
define("_SMALLINT", Sabel_Db_Type::SMALLINT);
define("_BIGINT",   Sabel_Db_Type::BIGINT);
define("_FLOAT",    Sabel_Db_Type::FLOAT);
define("_DOUBLE",   Sabel_Db_Type::DOUBLE);
define("_STRING",   Sabel_Db_Type::STRING);
define("_TEXT",     Sabel_Db_Type::TEXT);
define("_DATETIME", Sabel_Db_Type::DATETIME);
define("_DATE",     Sabel_Db_Type::DATE);
define("_BOOL",     Sabel_Db_Type::BOOL);
define("_BINARY",   Sabel_Db_Type::BINARY);
define("_NULL",     "SDB_NULL_VALUE");

/**
 * Sabel_Db_Migration_Manager
 *
 * @category   DB
 * @package    org.sabel.db
 * @author     Ebine Yutaka <yutaka@ebine.org>
 * @copyright  2004-2008 Mori Reo <mori.reo@sabel.jp>
 * @license    http://www.opensource.org/licenses/bsd-license.php  BSD License
 */
class Sabel_Db_Migration_Manager
{
  private static $metadata  = null;
  private static $stmt      = null;
  private static $directory = "";
  private static $applyMode = "";
  
  public static function setMetadata(Sabel_Db_Abstract_Metadata $metadata)
  {
    self::$metadata = $metadata;
  }
  
  public static function getMetadata()
  {
    return self::$metadata;
  }
  
  public static function setStatement(Sabel_Db_Statement $stmt)
  {
    self::$stmt = $stmt;
  }
  
  public static function getStatement()
  {
    self::$stmt->clear();
    
    return self::$stmt;
  }
  
  public static function setApplyMode($type)
  {
    self::$applyMode = $type;
  }
  
  public static function isUpgrade()
  {
    return (self::$applyMode === "upgrade");
  }
  
  public static function isDowngrade()
  {
    return (self::$applyMode === "downgrade");
  }
  
  public static function setDirectory($directory)
  {
    self::$directory = $directory;
  }
  
  public static function getDirectory()
  {
    return self::$directory;
  }
  
  public static function getFiles()
  {
    if (!is_dir(self::$directory)) {
      Sabel_Console::error("no such dirctory. '" . self::$directory . "'");
      exit;
    }
    
    $files = array();
    foreach (scandir(self::$directory) as $file) {
      $num = substr($file, 0, strpos($file, "_"));
      if (!is_numeric($num)) continue;
      
      if (isset($files[$num])) {
        Sabel_Console::error("the same version({$num}) files exists.");
        exit;
      } else {
        $files[$num] = $file;
      }
    }
    
    ksort($files);
    return $files;
  }
}
