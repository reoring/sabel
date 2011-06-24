<?php

/**
 * Sabel_Db_Pgsql_Migration
 *
 * @category   DB
 * @package    org.sabel.db
 * @author     Ebine Yutaka <ebine.yutaka@sabel.jp>
 * @copyright  2004-2008 Mori Reo <mori.reo@sabel.jp>
 * @license    http://www.opensource.org/licenses/bsd-license.php  BSD License
 */
class Sabel_Db_Pgsql_Migration extends Sabel_Db_Abstract_Migration
{
  protected $types = array(Sabel_Db_Type::INT      => "INTEGER",
                           Sabel_Db_Type::BIGINT   => "BIGINT",
                           Sabel_Db_Type::SMALLINT => "SMALLINT",
                           Sabel_Db_Type::FLOAT    => "REAL",
                           Sabel_Db_Type::DOUBLE   => "DOUBLE PRECISION",
                           Sabel_Db_Type::BOOL     => "BOOLEAN",
                           Sabel_Db_Type::STRING   => "VARCHAR",
                           Sabel_Db_Type::TEXT     => "TEXT",
                           Sabel_Db_Type::DATETIME => "TIMESTAMP",
                           Sabel_Db_Type::DATE     => "DATE",
                           Sabel_Db_Type::BINARY   => "BYTEA");
  
  protected function createTable($filePath)
  {
    $query = $this->getCreateSql($this->getReader($filePath)->readCreate());
    $this->executeQuery($query);
  }
  
  protected function changeColumnUpgrade($columns, $schema)
  {
    $this->alterChange($columns, $schema);
  }
  
  protected function changeColumnDowngrade($columns, $schema)
  {
    $this->alterChange($columns, $schema);
  }
  
  protected function createColumnAttributes($column)
  {
    $line   = array();
    $line[] = $this->quoteIdentifier($column->name);
    $line[] = $this->getTypeDefinition($column);
    
    if ($column->nullable === false) $line[] = "NOT NULL";
    $line[] = $this->getDefaultValue($column);
    
    return implode(" ", $line);
  }
  
  private function alterChange($columns, $schema)
  {
    $tblName = $this->quoteIdentifier($schema->getTableName());
    $stmt = $this->getStatement();
    $stmt->getDriver()->begin();
    
    foreach ($columns as $column) {
      $current = $schema->getColumnByName($column->name);
      if ($column->type !== null || ($current->isString() && $column->max !== null)) {
        $this->changeType($current, $column, $tblName);
      }
      
      if ($column->nullable !== null) {
        $this->changeNullable($current, $column, $tblName);
      }
      
      if ($column->default !== $current->default) {
        $this->changeDefault($column, $tblName);
      }
    }
    
    $stmt->getDriver()->commit();
  }
  
  private function changeType($current, $column, $tblName)
  {
    $colName = $this->quoteIdentifier($column->name);
    
    if ($current->type !== $column->type && $column->type !== null) {
      $type = $this->getTypeDefinition($column);
      $this->executeQuery("ALTER TABLE $tblName ALTER $colName TYPE $type");
    } elseif ($current->isString() && $current->max !== $column->max) {
      $column->type = $current->type;
      if ($column->max === null) $column->max = 255;
      $type = $this->getTypeDefinition($column);
      $this->executeQuery("ALTER TABLE $tblName ALTER $colName TYPE $type");
    }
  }
  
  private function changeNullable($current, $column, $tblName)
  {
    if ($current->nullable === $column->nullable) return;
    $colName = $this->quoteIdentifier($column->name);
    
    if ($column->nullable) {
      $this->executeQuery("ALTER TABLE $tblName ALTER $colName DROP NOT NULL");
    } else {
      $this->executeQuery("ALTER TABLE $tblName ALTER $colName SET NOT NULL");
    }
  }
  
  private function changeDefault($column, $tblName)
  {
    $colName = $this->quoteIdentifier($column->name);
    
    if ($column->default === _NULL) {
      $this->executeQuery("ALTER TABLE $tblName ALTER $colName DROP DEFAULT");
    } else {
      if ($column->isBool()) {
        $default = ($column->default) ? "true" : "false";
      } elseif ($column->isNumeric()) {
        $default = $column->default;
      } else {
        $default = "'{$column->default}'";
      }
      
      $this->executeQuery("ALTER TABLE $tblName ALTER $colName SET DEFAULT $default");
    }
  }
  
  private function getTypeDefinition($col)
  {
    if ($col->increment) {
      if ($col->isInt()) {
        return "serial";
      } elseif ($col->isBigint()) {
        return "bigserial";
      } else {
        throw new Sabel_Db_Exception("invalid data type for sequence.");
      }
    } elseif ($col->isString()) {
      return $this->types[$col->type] . "({$col->max})";
    } else {
      return $this->types[$col->type];
    }
  }
  
  protected function getBooleanAttr($value)
  {
    $v = ($value === true) ? "true" : "false";
    return "DEFAULT " . $v;
  }
}
