<?php

/**
 * Sabel_Db_Mssql_Migration
 *
 * @category   DB
 * @package    org.sabel.db
 * @author     Ebine Yutaka <ebine.yutaka@sabel.jp>
 * @copyright  2004-2008 Mori Reo <mori.reo@sabel.jp>
 * @license    http://www.opensource.org/licenses/bsd-license.php  BSD License
 */
class Sabel_Db_Mssql_Migration extends Sabel_Db_Abstract_Migration
{
  protected $types = array(Sabel_Db_Type::INT      => "INTEGER",
                           Sabel_Db_Type::BIGINT   => "BIGINT",
                           Sabel_Db_Type::SMALLINT => "SMALLINT",
                           Sabel_Db_Type::FLOAT    => "REAL",
                           Sabel_Db_Type::DOUBLE   => "DOUBLE PRECISION",
                           Sabel_Db_Type::BOOL     => "BIT",
                           Sabel_Db_Type::STRING   => "VARCHAR",
                           Sabel_Db_Type::TEXT     => "VARCHAR(MAX)",
                           Sabel_Db_Type::DATETIME => "DATETIME",
                           Sabel_Db_Type::DATE     => "DATETIME",
                           Sabel_Db_Type::BINARY   => "VARBINARY(MAX)");
  
  protected function createTable($filePath)
  {
    $query = $this->getCreateSql($this->getReader($filePath)->readCreate());
    $this->executeQuery($query);
  }
  
  protected function addColumn()
  {
    $columns = $this->getReader()->readAddColumn()->getColumns();
    
    if (Sabel_Db_Migration_Manager::isUpgrade()) {
      $this->execAddColumn($columns);
    } else {
      $tblName = convert_to_tablename($this->mdlName);
      $quotedTblName = $this->quoteIdentifier($tblName);
      foreach ($columns as $column) {
        $this->dropDefaultConstraint($tblName, $column->name);
        $colName = $this->quoteIdentifier($column->name);
        $this->executeQuery("ALTER TABLE $tblName DROP COLUMN $colName");
      }
    }
  }
  
  protected function changeColumnUpgrade($columns, $schema)
  {
    $tblName = $this->quoteIdentifier($schema->getTableName());
    
    foreach ($columns as $column) {
      $current = $schema->getColumnByName($column->name);
      $line = $this->alterChange($column, $current);
      $this->executeQuery("ALTER TABLE $tblName ALTER COLUMN $line");
    }
  }
  
  protected function changeColumnDowngrade($columns, $schema)
  {
    $tblName = $this->quoteIdentifier($schema->getTableName());
    
    foreach ($columns as $column) {
      $line = $this->createColumnAttributes($column);
      $this->executeQuery("ALTER TABLE $tblName ALTER COLUMN $line");
    }
  }
  
  protected function alterChange($column, $current)
  {
    $line   = array();
    $line[] = $this->quoteIdentifier($column->name);
    
    $c = ($column->type === null) ? $current : $column;
    $line[] = $this->getDataType($c, false);
    
    if ($c->isString()) {
      $max = ($column->max === null) ? $current->max : $column->max;
      $line[] = "({$max})";
    }
    
    $c = ($column->nullable === null) ? $current : $column;
    $line[] = ($c->nullable === false) ? "NOT NULL" : "NULL";
    
    /* @todo reset default constraint
    if (($d = $column->default) !== _NULL) {
      $cd = $current->default;
      
      if ($d === $cd) {
        $line[] = $this->getDefaultValue($current);
      } else {
        $this->valueCheck($column, $d);
        $line[] = $this->getDefaultValue($column);
      }
    }
    */
    
    if ($column->increment) $line[] = "IDENTITY(1, 1)";
    return implode(" ", $line);
  }
  
  protected function createColumnAttributes($column)
  {
    $line   = array();
    $line[] = $this->quoteIdentifier($column->name);
    $line[] = $this->getDataType($column);
    $line[] = ($column->nullable === false) ? "NOT NULL" : "NULL";
    $line[] = $this->getDefaultValue($column);
    
    if ($column->increment) $line[] = "IDENTITY(1, 1)";
    return implode(" ", $line);
  }
  
  private function getDataType($col, $withLength = true)
  {
    if (!$withLength) return $this->types[$col->type];
    
    if ($col->isString()) {
      return $this->types[$col->type] . "({$col->max})";
    } else {
      return $this->types[$col->type];
    }
  }
  
  protected function getBooleanAttr($value)
  {
    $v = ($value === true) ? "1" : "0";
    return "DEFAULT " . $v;
  }
  
  protected function dropDefaultConstraint($tblName, $colName)
  {
    $connectionName = $this->getStatement()->getDriver()->getConnectionName();
    $schemaName = Sabel_Db_Config::getSchemaName($connectionName);
    $cName = $this->getDefaultConstraintName($schemaName, $tblName, $colName);
    if ($cName === null) return;
    
    $quotedTblName = $this->quoteIdentifier($tblName);
    $this->executeQuery("ALTER TABLE $quotedTblName DROP CONSTRAINT $cName");
  }
  
  protected function getDefaultConstraintName($schemaName, $tblName, $colName)
  {
    $sql = <<<SQL
SELECT dc.name FROM sys.schemas s
  INNER JOIN sys.objects obj  ON obj.schema_id  = s.schema_id
  INNER JOIN sys.columns cols ON cols.object_id = obj.object_id
  INNER JOIN sys.default_constraints dc ON dc.object_id = cols.default_object_id
  WHERE s.name   = '{$schemaName}'
   AND obj.name  = '{$tblName}'
   AND cols.name = '{$colName}';
SQL;
    
    $rows = $this->executeQuery($sql);
    return (isset($rows[0]["name"])) ? $rows[0]["name"] : null;
  }
}
