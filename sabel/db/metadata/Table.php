<?php

/**
 * Sabel_Db_Metadata_Table
 *
 * @category   DB
 * @package    org.sabel.db
 * @author     Ebine Yutaka <ebine.yutaka@sabel.jp>
 * @copyright  2004-2008 Mori Reo <mori.reo@sabel.jp>
 * @license    http://www.opensource.org/licenses/bsd-license.php  BSD License
 */
class Sabel_Db_Metadata_Table extends Sabel_Object
{
  protected $tableName       = "";
  protected $columns         = array();
  protected $primaryKey      = null;
  protected $foreignKeys     = null;
  protected $uniques         = null;
  protected $incrementColumn = null;
  protected $tableEngine     = null;
  
  public function __construct($name, $columns)
  {
    $this->tableName = $name;
    $this->columns   = $columns;
    
    $this->setPrimaryKey();
    $this->setSequenceColumn();
  }
  
  public function setColumn(Sabel_Db_Metadata_Column $column)
  {
    $this->columns[$column->name] = $column;
  }
  
  public function __get($key)
  {
    return $this->getColumnByName($key);
  }
  
  public function getTableName()
  {
    return $this->tableName;
  }
  
  public function getColumns()
  {
    return $this->columns;
  }
  
  public function getColumnByName($name)
  {
    return (isset($this->columns[$name])) ? $this->columns[$name] : null;
  }
  
  public function getColumnNames()
  {
    return array_keys($this->columns);
  }
  
  public function hasColumn($name)
  {
    return isset($this->columns[$name]);
  }
  
  public function getPrimaryKey()
  {
    return $this->primaryKey;
  }
  
  public function getSequenceColumn()
  {
    return $this->incrementColumn;
  }
  
  public function setForeignKeys($fkeys)
  {
    if ($fkeys === null) return;
    $this->foreignKey = new Sabel_Db_Metadata_ForeignKey($fkeys);
  }
  
  public function getForeignKey()
  {
    return $this->foreignKey;
  }
  
  public function isForeignKey($colName)
  {
    return isset($this->foreignKeys[$colName]);
  }
  
  public function setUniques($uniques)
  {
    $this->uniques = $uniques;
  }
  
  public function getUniques()
  {
    return $this->uniques;
  }
  
  public function isUnique($colName)
  {
    $uniques = $this->uniques;
    if ($uniques === null) return false;
    
    foreach ($uniques as $unique) {
      if (in_array($colName, $unique)) return true;
    }
    
    return false;
  }
  
  public function setTableEngine($engine)
  {
    $this->tableEngine = $engine;
  }
  
  public function getTableEngine()
  {
    return $this->tableEngine;
  }
  
  private function setPrimaryKey()
  {
    $pkey = array();
    foreach ($this->columns as $column) {
      if ($column->primary) $pkey[] = $column->name;
    }
    
    switch (count($pkey)) {
      case 0:
        $this->primaryKey = null;
        break;
      
      case 1:
        $this->primaryKey = $pkey[0];
        break;
      
      default:
        $this->primaryKey = $pkey;
        break;
      
    }
  }
  
  private function setSequenceColumn()
  {
    $incrementColumn = null;
    
    foreach ($this->columns as $column) {
      if ($column->increment) {
        $incrementColumn = $column->name;
        break;
      }
    }
    
    $this->incrementColumn = $incrementColumn;
  }
}
