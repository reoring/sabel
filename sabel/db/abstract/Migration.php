<?php

/**
 * Sabel_Db_Abstract_Migration
 *
 * @abstract
 * @category   DB
 * @package    org.sabel.db
 * @author     Ebine Yutaka <ebine.yutaka@sabel.jp>
 * @copyright  2004-2008 Mori Reo <mori.reo@sabel.jp>
 * @license    http://www.opensource.org/licenses/bsd-license.php  BSD License
 */
abstract class Sabel_Db_Abstract_Migration extends Sabel_Object
{
  /**
   * @var string
   */
  protected $filePath = "";
  
  /**
   * @var string
   */
  protected $tblName = "";
  
  /**
   * @var string
   */
  protected $mdlName = "";
  
  /**
   * @var int
   */
  protected $version = 0;
  
  abstract protected function getBooleanAttr($value);
  
  public function execute($filePath)
  {
    clearstatcache();
    
    if (!is_file($filePath)) {
      $message = __METHOD__ . "() no such file or directory.";
      throw new Sabel_Exception_FileNotFound($message);
    }
    
    $this->filePath = $filePath;
    $file = basename($filePath);
    @list ($num, $mdlName, $command) = explode("_", $file);
    
    if ($mdlName === "query.php") {
      $command = "query";
    } else {
      $command = str_replace(".php", "", $command);
    }
    
    $this->version = $num;
    $this->mdlName = $mdlName;
    $this->tblName = convert_to_tablename($mdlName);
    
    if ($this->hasMethod($command)) {
      $this->$command();
    } else {
      $message = __METHOD__ . "() command '{$command}' not found.";
      throw new Sabel_Db_Exception($message);
    }
  }
  
  protected function create()
  {
    $tables = $this->getSchema()->getTableList();
    
    if (Sabel_Db_Migration_Manager::isUpgrade()) {
      if (in_array($this->tblName, $tables)) {
        Sabel_Console::warning("table '{$this->tblName}' already exists. (SKIP)");
      } else {
        $this->createTable($this->filePath);
      }
    } elseif (in_array($this->tblName, $tables, true)) {
      $this->executeQuery("DROP TABLE " . $this->quoteIdentifier($this->tblName));
    } else {
      Sabel_Console::warning("unknown table '{$this->tblName}'. (SKIP)");
    }
  }
  
  protected function drop()
  {
    $restore = $this->getRestoreFileName();
    
    if (Sabel_Db_Migration_Manager::isUpgrade()) {
      if (is_file($restore)) unlink($restore);
      $schema = $this->getSchema()->getTable($this->tblName);
      $writer = new Sabel_Db_Migration_Writer($restore);
      $writer->writeTable($schema);
      $this->executeQuery("DROP TABLE " . $this->quoteIdentifier($this->tblName));
    } else {
      $this->createTable($restore);
    }
  }
  
  protected function addColumn()
  {
    $columns = $this->getReader()->readAddColumn()->getColumns();
    
    if (Sabel_Db_Migration_Manager::isUpgrade()) {
      $this->execAddColumn($columns);
    } else {
      $quotedTblName = $this->quoteIdentifier($this->tblName);
      foreach ($columns as $column) {
        $colName = $this->quoteIdentifier($column->name);
        $this->executeQuery("ALTER TABLE $quotedTblName DROP COLUMN $colName");
      }
    }
  }
  
  protected function execAddColumn($columns)
  {
    $quotedTblName = $this->quoteIdentifier($this->tblName);
    $names = $this->getSchema()->getTable($this->tblName)->getColumnNames();
    
    foreach ($columns as $column) {
      if (in_array($column->name, $names)) {
        Sabel_Console::warning("duplicate column '{$column->name}'. (SKIP)");
      } else {
        $line = $this->createColumnAttributes($column);
        $this->executeQuery("ALTER TABLE $quotedTblName ADD $line");
      }
    }
  }
  
  protected function dropColumn()
  {
    $restore = $this->getRestoreFileName();
    
    if (Sabel_Db_Migration_Manager::isUpgrade()) {
      if (is_file($restore)) unlink($restore);
      
      $columns  = $this->getReader()->readDropColumn()->getColumns();
      $schema   = $this->getSchema()->getTable($this->tblName);
      $colNames = $schema->getColumnNames();
      
      $writer = new Sabel_Db_Migration_Writer($restore);
      $writer->writeColumns($schema, $columns)->close();
      $quotedTblName = $this->quoteIdentifier($this->tblName);
      
      foreach ($columns as $column) {
        if (in_array($column, $colNames)) {
          $colName = $this->quoteIdentifier($column);
          $this->executeQuery("ALTER TABLE $quotedTblName DROP COLUMN $colName");
        } else {
          Sabel_Console::warning("column '{$column}' does not exist. (SKIP)");
        }
      }
    } else {
      $columns = $this->getReader($restore)->readAddColumn()->getColumns();
      $this->execAddColumn($columns);
    }
  }
  
  protected function changeColumn()
  {
    $schema  = $this->getSchema()->getTable($this->tblName);
    $restore = $this->getRestoreFileName();
    
    if (Sabel_Db_Migration_Manager::isUpgrade()) {
      if (is_file($restore)) unlink($restore);
      
      $names = array();
      $columns = $this->getReader()->readChangeColumn()->getColumns();
      foreach ($columns as $column) $names[] = $column->name;
      
      $writer = new Sabel_Db_Migration_Writer($restore);
      $writer->writeColumns($schema, $names, '$change')->close();
      $this->changeColumnUpgrade($columns, $schema);
    } else {
      $columns = $this->getReader($restore)->readChangeColumn()->getColumns();
      $this->changeColumnDowngrade($columns, $schema);
    }
  }
  
  protected function getCreateSql($create)
  {
    $query = array();
    foreach ($create->getColumns() as $column) {
      $query[] = $this->createColumnAttributes($column);
    }
    
    if ($pkeys = $create->getPrimaryKeys()) {
      $cols = $this->quoteIdentifier($pkeys);
      $query[] = "PRIMARY KEY(" . implode(", ", $cols) . ")";
    }
    
    if ($fkeys = $create->getForeignKeys()) {
      foreach ($fkeys as $fkey) {
        $query[] = $this->createForeignKey($fkey->get());
      }
    }
    
    if ($uniques = $create->getUniques()) {
      foreach ($uniques as $unique) {
        $cols = $this->quoteIdentifier($unique);
        $query[] = "UNIQUE (" . implode(", ", $cols) . ")";
      }
    }
    
    $quotedTblName = $this->quoteIdentifier($this->tblName);
    return "CREATE TABLE $quotedTblName (" . implode(", ", $query) . ")";
  }
  
  protected function createForeignKey($object)
  {
    $query = "FOREIGN KEY ({$this->quoteIdentifier($object->column)}) "
           . "REFERENCES {$this->quoteIdentifier($object->refTable)}"
           . "({$this->quoteIdentifier($object->refColumn)})";
    
    if ($object->onDelete !== null) {
      $query .= " ON DELETE " . $object->onDelete;
    }
    
    if ($object->onUpdate !== null) {
      $query .= " ON UPDATE " . $object->onUpdate;
    }
    
    return $query;
  }
  
  protected function getRestoreFileName()
  {
    $directory = Sabel_Db_Migration_Manager::getDirectory();
    $dir = $directory . DIRECTORY_SEPARATOR . "restores";
    if (!is_dir($dir)) mkdir($dir);
    
    return $dir . DIRECTORY_SEPARATOR . "restore_" . $this->version . ".php";
  }
  
  protected function query()
  {
    $this->getReader()->readQuery()->execute();
  }
  
  protected function getReader($filePath = null)
  {
    if ($filePath === null) $filePath = $this->filePath;
    return new Sabel_Db_Migration_Reader($filePath);
  }
  
  protected function getSchema()
  {
    return Sabel_Db_Migration_Manager::getMetadata();
  }
  
  protected function getStatement()
  {
    return Sabel_Db_Migration_Manager::getStatement();
  }
  
  protected function executeQuery($query)
  {
    return $this->getStatement()->setQuery($query)->execute();
  }
  
  protected function quoteIdentifier($arg)
  {
    return $this->getStatement()->quoteIdentifier($arg);
  }
  
  protected function getDefaultValue($column)
  {
    $d = $column->default;
    
    if ($column->isBool()) {
      return $this->getBooleanAttr($d);
    } elseif ($d === null || $d === _NULL) {
      return "";
    } elseif ($column->isNumeric() && !$column->isBigint()) {
      return "DEFAULT $d";
    } else {
      return "DEFAULT '{$d}'";
    }
  }
}
