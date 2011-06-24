<?php

/**
 * Sabel_Db_Mysql_Migration
 *
 * @category   DB
 * @package    org.sabel.db
 * @author     Ebine Yutaka <ebine.yutaka@sabel.jp>
 * @copyright  2004-2008 Mori Reo <mori.reo@sabel.jp>
 * @license    http://www.opensource.org/licenses/bsd-license.php  BSD License
 */
class Sabel_Db_Mysql_Migration extends Sabel_Db_Abstract_Migration
{
  protected $types = array(Sabel_Db_Type::INT      => "INTEGER",
                           Sabel_Db_Type::BIGINT   => "BIGINT",
                           Sabel_Db_Type::SMALLINT => "SMALLINT",
                           Sabel_Db_Type::FLOAT    => "FLOAT",
                           Sabel_Db_Type::DOUBLE   => "DOUBLE",
                           Sabel_Db_Type::BOOL     => "TINYINT(1)",
                           Sabel_Db_Type::STRING   => "VARCHAR",
                           Sabel_Db_Type::TEXT     => "TEXT",
                           Sabel_Db_Type::DATETIME => "DATETIME",
                           Sabel_Db_Type::DATE     => "DATE",
                           Sabel_Db_Type::BINARY   => "LONGBLOB");
  
  protected function createTable($filePath)
  {
    $create  = $this->getReader($filePath)->readCreate();
    $query   = $this->getCreateSql($create);
    $options = $create->getOptions();
    
    if (isset($options["engine"])) {
      $query .= " ENGINE=" . $options["engine"];
    }
    
    $this->executeQuery($query);
    
    if ($indexes = $create->getIndexes()) {
      $this->createIndex($indexes);
    }
  }
  
  public function drop()
  {
    if (Sabel_Db_Migration_Manager::isUpgrade()) {
      $restore = $this->getRestoreFileName();
      if (is_file($restore)) unlink($restore);
      
      $schema    = $this->getSchema();
      $tblSchema = $schema->getTable($this->tblName);
      $engine    = $schema->getTableEngine($this->tblName);
      
      $writer = new Sabel_Db_Migration_Writer($restore);
      $writer->writeTable($tblSchema);
      $writer->write('$create->options("engine", "' . $engine . '");');
      $writer->write(PHP_EOL)->close();
      
      $this->executeQuery("DROP TABLE " . $this->quoteIdentifier($this->tblName));
    } else {
      $this->createTable($this->getRestoreFileName());
    }
  }
  
  protected function changeColumnUpgrade($columns, $schema)
  {
    $quotedTblName = $this->quoteIdentifier($this->tblName);
    
    foreach ($columns as $column) {
      $current = $schema->getColumnByName($column->name);
      $line = $this->alterChange($column, $current);
      $this->executeQuery("ALTER TABLE $quotedTblName MODIFY $line");
    }
  }
  
  protected function changeColumnDowngrade($columns, $schema)
  {
    $quotedTblName = $this->quoteIdentifier($this->tblName);
    
    foreach ($columns as $column) {
      $line = $this->createColumnAttributes($column);
      $this->executeQuery("ALTER TABLE $quotedTblName MODIFY $line");
    }
  }
  
  protected function createColumnAttributes($col)
  {
    $line   = array();
    $line[] = $this->quoteIdentifier($col->name);
    $line[] = $this->getTypeDefinition($col);
    $line[] = $this->getNullableDefinition($col);
    $line[] = $this->getDefaultValue($col);
    
    if ($col->increment) $line[] = "AUTO_INCREMENT";
    return implode(" ", $line);
  }
  
  protected function alterChange($column, $current)
  {
    $line   = array();
    $line[] = $this->quoteIdentifier($column->name);
    
    $c = ($column->type === null) ? $current : $column;
    $line[] = $this->getTypeDefinition($c, false);
    
    if ($c->isString()) {
      $max = ($column->max === null) ? $current->max : $column->max;
      $line[] = "({$max})";
    }
    
    $c = ($column->nullable === null) ? $current : $column;
    $line[] = $this->getNullableDefinition($c);
    
    if (($d = $column->default) !== _NULL) {
      $cd = $current->default;
      
      if ($d === $cd) {
        $line[] = $this->getDefaultValue($current);
      } else {
        $this->valueCheck($column, $d);
        $line[] = $this->getDefaultValue($column);
      }
    }
    
    if ($column->increment) $line[] = "AUTO_INCREMENT";
    return implode(" ", $line);
  }
  
  protected function getTypeDefinition($col, $withLength = true)
  {
    if (!$withLength) return $this->types[$col->type];
    
    if ($col->isString()) {
      return $this->types[$col->type] . "({$col->max})";
    } else {
      return $this->types[$col->type];
    }
  }
  
  protected function getNullableDefinition($column)
  {
    return ($column->nullable === false) ? "NOT NULL" : "";
  }
  
  protected function getDefaultValue($column)
  {
    $d = $column->default;
    
    if ($column->isBool()) {
      return $this->getBooleanAttr($d);
    } elseif ($d === null || $d === _NULL) {
      if ($column->isString()) {
        return ($column->nullable === true) ? "DEFAULT ''" : "";
      } else {
        return ($column->nullable === true) ? "DEFAULT NULL" : "";
      }
    } elseif ($column->isNumeric()) {
      return "DEFAULT $d";
    } else {
      return "DEFAULT '{$d}'";
    }
  }
  
  protected function getBooleanAttr($value)
  {
    $value = ($value === true) ? "1" : "0";
    return "DEFAULT " . $value;
  }
  
  private function valueCheck($column, $default)
  {
    if ($default === null) return true;
    
    if (($column->isBool() && !is_bool($default)) ||
        ($column->isNumeric() && !is_numeric($default))) {
      $message = __METHOD__ . "() invalid default value for '{$column->name}'.";
      throw new Sabel_Db_Exception($message);
    } else {
      return true;
    }
  }
}
