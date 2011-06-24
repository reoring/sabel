<?php

/**
 * Sabel_Db_Config
 *
 * @category   DB
 * @package    org.sabel.db
 * @author     Ebine Yutaka <ebine.yutaka@sabel.jp>
 * @copyright  2004-2008 Mori Reo <mori.reo@sabel.jp>
 * @license    http://www.opensource.org/licenses/bsd-license.php  BSD License
 */
class Sabel_Db_Config
{
  private static $initialized = false;
  private static $configs = array();
  
  public static function initialize(Sabel_Config $config)
  {
    if (self::$initialized) return;
    
    foreach ($config->configure() as $connectionName => $params) {
      self::$configs[$connectionName] = $params;
    }
    
    self::$initialized = true;
  }
  
  public static function add($connectionName, $params)
  {
    self::$configs[$connectionName] = $params;
  }
  
  public static function get($connectionName = null)
  {
    if ($connectionName === null) {
      return self::$configs;
    } else {
      return self::getConfig($connectionName);
    }
  }
  
  public static function getPackage($connectionName)
  {
    $config = self::getConfig($connectionName);
    
    if (isset($config["package"])) {
      return $config["package"];
    } else {
      $message = "'package' not found in config.";
      throw new Sabel_Db_Exception($message);
    }
  }
  
  public static function getSchemaName($connectionName)
  {
    // @todo improvement
    
    $package = self::getPackage($connectionName);
    $ignores = array("sabel.db.pdo.sqlite" => 1, "sabel.db.ibase" => 1);
    if (isset($ignores[$package])) return null;
    
    $config = self::getConfig($connectionName);
    
    if (isset($config["schema"])) {
      return $config["schema"];
    } elseif (strpos($package, "mysql") !== false) {
      return $config["database"];
    } elseif (strpos($package, "pgsql") !== false) {
      return "public";
    } elseif (strpos($package, "oci")   !== false) {
      return strtoupper($config["user"]);
    } elseif (strpos($package, "mssql") !== false) {
      return "dbo";
    } else {
      $message = __METHOD__ . "() 'schema' not found in config.";
      throw new Sabel_Db_Exception($message);
    }
  }
  
  public static function getConnectionNamesOfSameSetting($connectionName)
  {
    $names = array();
    foreach (self::$configs as $name => $setting) {
      if ($name === $connectionName) continue;
      if ($setting == self::$configs[$connectionName]) $names[] = $name;
    }
    
    return $names;
  }
  
  private static function getConfig($connectionName)
  {
    if (isset(self::$configs[$connectionName])) {
      return self::$configs[$connectionName];
    } else {
      $message = "getConfig() config for '{$connectionName}' not found.";
      throw new Sabel_Db_Exception($message);
    }
  }
}
