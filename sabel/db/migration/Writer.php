<?php

/**
 * Sabel_Db_Migration_Writer
 *
 * @category   DB
 * @package    org.sabel.db
 * @author     Ebine Yutaka <ebine.yutaka@sabel.jp>
 * @copyright  2004-2008 Mori Reo <mori.reo@sabel.jp>
 * @license    http://www.opensource.org/licenses/bsd-license.php  BSD License
 */
class Sabel_Db_Migration_Writer
{
  private $fp = null;
  
  public function __construct($filePath)
  {
    $this->fp = fopen($filePath, "w");
  }
  
  public function write($line)
  {
    fwrite($this->fp, $line);
    
    return $this;
  }
  
  public function close()
  {
    fclose($this->fp);
  }
  
  public function writeTable($schema)
  {
    $fp = $this->fp;
    $columns = $schema->getColumns();
    $this->_writeColumns($columns, '$create');
    $pkey = $schema->getPrimarykey();
    
    if (is_array($pkey)) {
      $pkeys = array();
      foreach ($pkey as $key) $pkeys[] = '"' . $key .'"';
      $this->write('$create->primary(array(' . implode(", ", $pkeys) . '));' . PHP_EOL);
    }
    
    $uniques = $schema->getUniques();
    
    if ($uniques) {
      foreach ($uniques as $unique) {
        if (count($unique) === 1) {
          $this->write('$create->unique("' . $unique[0] . '");');
        } else {
          $us = array();
          foreach ($unique as $u) $us[] = '"' . $u . '"';
          $this->write('$create->unique(array(' . implode(", ", $us) . '));');
        }
        
        $this->write(PHP_EOL);
      }
    }
    
    if ($fkey = $schema->getForeignKey()) {
      foreach ($fkey->toArray() as $colName => $param) {
        $line = '$create->fkey("' . $colName . '")->table("'
              . $param->table    . '")->column("'
              . $param->column   . '")->onDelete("'
              . $param->onDelete . '")->onUpdate("'
              . $param->onUpdate . '");';
        
        $this->write($line . PHP_EOL);
      }
    }
    
    return $this;
  }
  
  public function writeColumns($schema, $alterCols, $variable = '$add')
  {
    $columns = array();
    
    foreach ($schema->getColumns() as $column) {
      if (in_array($column->name, $alterCols)) $columns[] = $column;
    }
    
    $this->_writeColumns($columns, $variable);
    
    return $this;
  }
  
  private function _writeColumns($columns, $variable)
  {
    $lines = array();
    foreach ($columns as $column) {
      $line = array($variable);
      $line[] = '->column("' . $column->name . '")';
      $line[] = '->type(' . $column->type . ')';
      
      $bool = ($column->nullable) ? "true" : "false";
      $line[] = '->nullable(' . $bool . ')';
      
      $bool = ($column->primary) ? "true" : "false";
      $line[] = '->primary(' . $bool . ')';
      
      $bool = ($column->increment) ? "true" : "false";
      $line[] = '->increment(' . $bool . ')';
      
      if ($column->default === null) {
        $line[] = '->value(_NULL)';
      } else {
        if ($column->isNumeric()) {
          $line[] = '->value(' . $column->default . ')';
        } elseif ($column->isBool()) {
          $bool = ($column->default) ? "true" : "false";
          $line[] = '->value(' . $bool . ')';
        } else {
          $line[] = '->value("' . $column->default . '")';
        }
      }
      
      if ($column->isString()) {
        $line[] = '->length(' . $column->max. ')';
      }
      
      $line[]  = ";";
      $lines[] = implode("", $line);
    }
    
    $this->write("<?php" . PHP_EOL . PHP_EOL);
    $this->write(implode(PHP_EOL, $lines));
    $this->write(PHP_EOL . PHP_EOL);
  }
}
