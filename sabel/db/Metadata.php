<?php

/**
 * Sabel_Db_Metadata
 *
 * @category   DB
 * @package    org.sabel.db
 * @author     Ebine Yutaka <ebine.yutaka@sabel.jp>
 * @copyright  2004-2008 Mori Reo <mori.reo@sabel.jp>
 * @license    http://www.opensource.org/licenses/bsd-license.php  BSD License
 */
class Sabel_Db_Metadata
{
  private static $metadata = array();
  
  public static function getTableInfo($tblName, $connectionName = "default")
  {
    if (isset(self::$metadata[$tblName])) {
      return self::$metadata[$tblName];
    }
    
    if (self::schemaClassExists($tblName)) {
      $cols = array();
      $className = "Schema_" . convert_to_modelname($tblName);
      $schemaClass = new $className();
      foreach ($schemaClass->get() as $colName => $info) {
        $co = new Sabel_Db_Metadata_Column();
        $co->name = $colName;
        foreach ($info as $key => $val) $co->$key = $val;
        $cols[$colName] = $co;
      }
      
      $tblSchema  = new Sabel_Db_Metadata_Table($tblName, $cols);
      $properties = $schemaClass->getProperty();
      $tblSchema->setTableEngine($properties["tableEngine"]);
      $tblSchema->setUniques($properties["uniques"]);
      $tblSchema->setForeignKeys($properties["fkeys"]);
    } else {
      $schemaObj = Sabel_Db::createMetadata($connectionName);
      $tblSchema = $schemaObj->getTable($tblName);
    }
    
    return self::$metadata[$tblName] = $tblSchema;
  }
  
  public static function schemaClassExists($tblName)
  {
    $className = "Schema_" . convert_to_modelname($tblName);
    return Sabel::using($className);
  }
  
  public static function getTableList($connectionName = "default")
  {
    $className = "Schema_" . ucfirst($connectionName) . "TableList";
    
    if (Sabel::using($className)) {
      $sc = new $className();
      return $sc->get();
    } else {
      return Sabel_Db::createMetadata($connectionName)->getTableList();
    }
  }
  
  public static function clear()
  {
    $metadata = self::$metadata;
    self::$metadata = array();
    
    return $metadata;
  }
}
