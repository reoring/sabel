<?php

/**
 * Sabel_Db_Abstract_Metadata
 *
 * @abstract
 * @category   DB
 * @package    org.sabel.db
 * @author     Ebine Yutaka <ebine.yutaka@sabel.jp>
 * @copyright  2004-2008 Mori Reo <mori.reo@sabel.jp>
 * @license    http://www.opensource.org/licenses/bsd-license.php  BSD License
 */
abstract class Sabel_Db_Abstract_Metadata extends Sabel_Object
{
  protected $driver = null;
  protected $schemaName = "";
  
  abstract public function getTableList();
  abstract public function getForeignKeys($tblName);
  abstract public function getUniques($tblName);
  
  public function __construct(Sabel_Db_Driver $driver, $schemaName)
  {
    $this->driver = $driver;
    $this->schemaName = $schemaName;
  }
  
  public function getAll()
  {
    $tables = array();
    foreach ($this->getTableList() as $tblName) {
      $tables[$tblName] = $this->getTable($tblName);
    }
    
    return $tables;
  }
  
  public function getTable($tblName)
  {
    $columns = $this->createColumns($tblName);
    $schema  = new Sabel_Db_Metadata_Table($tblName, $columns);
    $schema->setForeignKeys($this->getForeignKeys($tblName));
    $schema->setUniques($this->getUniques($tblName));
    
    return $schema;
  }
  
  protected function setDefaultValue($column, $default)
  {
    if ($default === null ||
        (is_string($default) &&
        ($default === "" || strtolower($default) === "null"))
       ) {
      $column->default = null;
      return;
    }
    
    switch ($column->type) {
      case Sabel_Db_Type::INT:
      case Sabel_Db_Type::SMALLINT:
        $column->default = (int)$default;
        break;
        
      case Sabel_Db_Type::FLOAT:
      case Sabel_Db_Type::DOUBLE:
        $column->default = (float)$default;
        break;
        
      case Sabel_Db_Type::BOOL:
        if (is_bool($default)) {
          $column->default = $default;
        } else {
          $column->default = in_array($default, array("1", "t", "true"));
        }
        break;
        
      case Sabel_Db_Type::BIGINT:
        $column->default = (string)$default;
        break;
        
      default:
        $column->default = $default;
    }
  }
  
  public function getTableEngine($tblName)
  {
    return null;
  }
}
