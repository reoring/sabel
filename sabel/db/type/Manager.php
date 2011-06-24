<?php

/**
 * Sabel_Db_Type_Manager
 *
 * @category   DB
 * @package    org.sabel.db
 * @author     Ebine Yutaka <ebine.yutaka@sabel.jp>
 * @copyright  2004-2008 Mori Reo <mori.reo@sabel.jp>
 * @license    http://www.opensource.org/licenses/bsd-license.php  BSD License
 */
class Sabel_Db_Type_Manager
{
  private static $instance = null;
  
  private function __construct()
  {
    
  }
  
  public static function create()
  {
    if (self::$instance === null) {
      self::$instance = new self();
    }
    
    return self::$instance;
  }
  
  public function applyType(Sabel_Db_Metadata_Column $column, $type)
  {
    $methods = array(
      "_int",      "_bigint", "_smallint",
      "_string",   "_text",   "_boolean",
      "_datetime", "_date",   "_double",
      "_float",    "_binary"
    );
    
    $type = strtolower($type);
    foreach ($methods as $method) {
      if ($this->$method($column, $type)) {
        return;
      }
    }
    
    $column->type = Sabel_Db_Type::UNKNOWN;
  }
  
  protected function _int($column, $type)
  {
    $types = array("integer", "int", "int4", "serial", "tinyint");
    
    if (in_array($type, $types, true)) {
      $column->type = Sabel_Db_Type::INT;
      $column->max  = PHP_INT_MAX;
      $column->min  = -PHP_INT_MAX - 1;
      return true;
    } else {
      return false;
    }
  }
  
  protected function _bigint($column, $type)
  {
    $types = array("bigint", "int8", "bigserial");
    
    if (in_array($type, $types, true)) {
      $column->type = Sabel_Db_Type::BIGINT;
      $column->max  =  9223372036854775807;
      $column->min  = -9223372036854775808;
      return true;
    } else {
      return false;
    }
  }
  
  protected function _smallint($column, $type)
  {
    if ($type === "smallint") {
      $column->type = Sabel_Db_Type::SMALLINT;
      $column->max  = 32767;
      $column->min  = -32768;
      return true;
    } else {
      return false;
    }
  }
  
  protected function _string($column, $type)
  {
    $types = array(
      "varchar", "char", "character varying",
      "character", "varchar2", "cstring"
    );
    
    if (in_array($type, $types, true)) {
      $column->type = Sabel_Db_Type::STRING;
      return true;
    } else {
      return false;
    }
  }
  
  protected function _text($column, $type)
  {
    $types = array(
      "text", "clob", "mediumtext",
      "tinytext", "nclob"
    );
    
    if (in_array($type, $types, true)) {
      $column->type = Sabel_Db_Type::TEXT;
      return true;
    } else {
      return false;
    }
  }
  
  protected function _boolean($column, $type)
  {
    if ($type === "boolean" || $type === "bit") {
      $column->type = Sabel_Db_Type::BOOL;
      return true;
    } else {
      return false;
    }
  }
  
  protected function _datetime($column, $type)
  {
    $types = array(
      "timestamp", "timestamp without time zone",
      "datetime" , "timestamp with time zone"
    );
    
    if (in_array($type, $types, true)) {
      $column->type = Sabel_Db_Type::DATETIME;
      return true;
    } else {
      return false;
    }
  }
  
  protected function _date($column, $type)
  {
    if ($type === "date") {
      $column->type = Sabel_Db_Type::DATE;
      return true;
    } else {
      return false;
    }
  }
  
  protected function _double($column, $type)
  {
    $types = array("double", "double precision", "float8");
    
    if (in_array($type, $types, true)) {
      $column->type = Sabel_Db_Type::DOUBLE;
      $column->max  =  1.79769E+308;
      $column->min  = -1.79769E+308;
      return true;
    } else {
      return false;
    }
  }
  
  protected function _float($column, $type)
  {
    $types = array("float", "real", "float4");
    
    if (in_array($type, $types, true)) {
      $column->type = Sabel_Db_Type::FLOAT;
      $column->max  =  3.4028235E+38;
      $column->min  = -3.4028235E+38;
      return true;
    } else {
      return false;
    }
  }
  
  protected function _binary($column, $type)
  {
    $types = array(
      "blob",  "longblob", "mediumblob",
      "bytea", "varbinary", "binary"
    );
    
    if (in_array($type, $types, true)) {
      $column->type = Sabel_Db_Type::BINARY;
      return true;
    } else {
      return false;
    }
  }
}
