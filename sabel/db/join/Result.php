<?php

/**
 * Sabel_Db_Join_Result
 *
 * @category   DB
 * @package    org.sabel.db
 * @author     Ebine Yutaka <ebine.yutaka@sabel.jp>
 * @copyright  2004-2008 Mori Reo <mori.reo@sabel.jp>
 * @license    http://www.opensource.org/licenses/bsd-license.php  BSD License
 */
class Sabel_Db_Join_Result
{
  public static function build(Sabel_Db_Model $source, Sabel_Db_Join_Structure $structure, $rows)
  {
    $objects = $structure->getJoinObjects();
    $structure = $structure->getStructure();
    
    $tables = array();
    foreach ($structure as $joinTables) {
      $tables = array_merge($tables, $joinTables);
    }
    
    $results = array();
    $selfObj = MODEL($source->getName());
    
    foreach ($rows as $row) {
      $models = self::createModels($row, $tables, $objects);
      
      foreach ($tables as $tblName) {
        if (!isset($structure[$tblName])) continue;
        foreach ($structure[$tblName] as $parent) {
          $name = convert_to_modelname($parent);
          $models[$tblName]->__set($name, $models[$parent]);
        }
      }
      
      $self = clone $selfObj;
      $self->setProperties($row);
      
      $tblName = $source->getTableName();
      foreach ($structure[$tblName] as $parent) {
        $name = convert_to_modelname($parent);
        $self->__set($name, $models[$parent]);
      }
      
      $results[] = $self;
    }
    
    return $results;
  }
  
  private static function createModels(&$row, $tables, $objects)
  {
    $models = array();
    foreach ($tables as $tblName) {
      $object = $objects[$tblName];
      $model  = $object->createModel($row);
      $models[$tblName] = $model;
    }
    
    return $models;
  }
}
