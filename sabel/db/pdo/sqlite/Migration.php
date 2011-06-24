<?php

/**
 * Sabel_Db_Pdo_Sqlite_Migration
 *
 * @category   DB
 * @package    org.sabel.db.pdo
 * @author     Ebine Yutaka <ebine.yutaka@sabel.jp>
 * @copyright  2004-2008 Mori Reo <mori.reo@sabel.jp>
 * @license    http://www.opensource.org/licenses/bsd-license.php  BSD License
 */
class Sabel_Db_Pdo_Sqlite_Migration extends Sabel_Db_Abstract_Migration
{
  protected $types = array(Sabel_Db_Type::INT      => "int",
                           Sabel_Db_Type::BIGINT   => "bigint",
                           Sabel_Db_Type::SMALLINT => "smallint",
                           Sabel_Db_Type::FLOAT    => "float",
                           Sabel_Db_Type::DOUBLE   => "double",
                           Sabel_Db_Type::BOOL     => "boolean",
                           Sabel_Db_Type::STRING   => "varchar",
                           Sabel_Db_Type::TEXT     => "text",
                           Sabel_Db_Type::DATETIME => "datetime",
                           Sabel_Db_Type::DATE     => "date",
                           Sabel_Db_Type::BINARY   => "binary");
  
  protected function createTable($filePath)
  {
    $create  = $this->getReader($filePath)->readCreate();
    $columns = $create->getColumns();
    $pkeys   = $create->getPrimaryKeys();
    $uniques = $create->getUniques();
    $query   = $this->makeCreateSql($columns, $pkeys, $uniques);
    
    $this->executeQuery($query);
  }
  
  protected function addColumn()
  {
    $columns = $this->getReader()->readAddColumn()->getColumns();
    
    if (Sabel_Db_Migration_Manager::isUpgrade()) {
      $this->execAddColumn($columns);
    } else {
      $schema = $this->getSchema()->getTable($this->tblName);
      $currents = $schema->getColumns();
      
      foreach ($columns as $column) {
        $name = $column->name;
        if (isset($currents[$name])) unset($currents[$name]);
      }
      
      $this->dropColumnsAndRemakeTable($currents, $schema);
    }
  }
  
  protected function dropColumn()
  {
    $restore = $this->getRestoreFileName();
    
    if (Sabel_Db_Migration_Manager::isUpgrade()) {
      if (is_file($restore)) unlink($restore);
      
      $columns  = $this->getReader()->readDropColumn()->getColumns();
      $schema   = $this->getSchema()->getTable($this->tblName);
      $sColumns = $schema->getColumns();
      
      $writer = new Sabel_Db_Migration_Writer($restore);
      $writer->writeColumns($schema, $columns);
      $writer->close();
      
      foreach ($columns as $column) {
        if (isset($sColumns[$column])) {
          unset($sColumns[$column]);
        } else {
          $warning = "column '{$column}' does not exist. (SKIP)";
          Sabel_Console::warning($warning);
        }
      }
      
      $this->dropColumnsAndRemakeTable($sColumns, $schema);
    } else {
      $columns = $this->getReader($restore)->readAddColumn()->getColumns();
      $this->execAddColumn($columns);
    }
  }
  
  protected function changeColumnUpgrade($columns, $schema)
  {
    $sColumns = $schema->getColumns();
    
    foreach ($columns as $column) {
      if (isset($sColumns[$column->name])) {
        $column = $this->alterChange($column, $sColumns[$column->name]);
        $sColumns[$column->name] = $column;
      }
    }
    
    $this->dropColumnsAndRemakeTable($sColumns, $schema);
  }
  
  protected function changeColumnDowngrade($columns, $schema)
  {
    $sColumns = $schema->getColumns();
    
    foreach ($columns as $column) {
      if (isset($sColumns[$column->name])) $sColumns[$column->name] = $column;
    }
    
    $this->dropColumnsAndRemakeTable($sColumns, $schema);
  }
  
  protected function createColumnAttributes($col)
  {
    $line = array($this->quoteIdentifier($col->name));
    
    if ($col->increment) {
      $line[] = "integer PRIMARY KEY";
    } elseif ($col->isString()) {
      $line[] = $this->types[$col->type] . "({$col->max})";
    } else {
      $line[] = $this->types[$col->type];
    }
    
    if ($col->nullable === false) $line[] = "NOT NULL";
    $line[] = $this->getDefaultValue($col);
    
    return implode(" ", $line);
  }
  
  private function dropColumnsAndRemakeTable($columns, $schema)
  {
    $stmt    = $this->getStatement();
    $pkeys   = $schema->getPrimaryKey();
    $uniques = $schema->getUniques();
    $query   = $this->makeCreateSql($columns, $pkeys, $uniques);
    
    $quotedTblName = $stmt->quoteIdentifier($this->tblName);
    $tmpTblName = "sbl_tmp_{$this->tblName}";
    $query = str_replace(" TABLE $quotedTblName", " TABLE $tmpTblName", $query);
    
    $stmt->getDriver()->begin();
    $stmt->setQuery($query)->execute();
    
    $projection = array();
    foreach (array_keys($columns) as $key) $projection[] = $key;
    
    $projection = implode(", ", $stmt->quoteIdentifier($projection));
    $query = "INSERT INTO $tmpTblName SELECT $projection FROM $quotedTblName";
    
    $stmt->setQuery($query)->execute();
    $stmt->setQuery("DROP TABLE $quotedTblName")->execute();
    $stmt->setQuery("ALTER TABLE $tmpTblName RENAME TO $quotedTblName")->execute();
    $stmt->getDriver()->commit();
  }
  
  private function alterChange($column, $current)
  {
    if ($column->type === null) {
      $column->type = $current->type;
    }
    
    if ($column->isString() && $column->max === null) {
      $column->max = $current->max;
    }
    
    if ($column->nullable === null) {
      $column->nullable = $current->nullable;
    }
    
    if ($column->default === _NULL) {
      $column->default = null;
    } elseif ($column->default === null) {
      $column->default = $current->default;
    }
    
    return $column;
  }
  
  private function makeCreateSql($columns, $pkeys, $uniques)
  {
    $query  = array();
    $hasSeq = false;
    
    foreach ($columns as $column) {
      if ($column->increment) $hasSeq = true;
      $query[] = $this->createColumnAttributes($column);
    }
    
    if ($pkeys && !$hasSeq) {
      $cols = $this->quoteIdentifier($pkeys);
      $query[] = "PRIMARY KEY(" . implode(", ", $cols) . ")";
    }
    
    if ($uniques) {
      foreach ($uniques as $unique) {
        $cols = $this->quoteIdentifier($unique);
        $query[] = "UNIQUE (" . implode(", ", $cols) . ")";
      }
    }
    
    $quotedTblName = $this->quoteIdentifier($this->tblName);
    return "CREATE TABLE $quotedTblName (" . implode(", ", $query) . ")";
  }
  
  protected function getBooleanAttr($value)
  {
    $v = ($value === true) ? "true" : "false";
    return "DEFAULT " . $v;
  }
}
