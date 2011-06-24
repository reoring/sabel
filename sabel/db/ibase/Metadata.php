<?php

/**
 * Sabel_Db_Ibase_Metadata
 *
 * @category   DB
 * @package    org.sabel.db
 * @author     Ebine Yutaka <ebine.yutaka@sabel.jp>
 * @copyright  2004-2008 Mori Reo <mori.reo@sabel.jp>
 * @license    http://www.opensource.org/licenses/bsd-license.php  BSD License
 */
class Sabel_Db_Ibase_Metadata extends Sabel_Db_Abstract_Metadata
{
  /**
   * @var array
   */
  private $sequences = array();
  
  /**
   * @var array
   */
  private $primaryKeys = array();
  
  private
    $types = array("7"   => "smallint",
                   "8"   => "integer",
                   "10"  => "float",
                   "12"  => "date",
                   "13"  => "time",
                   "14"  => "char",
                   "16"  => "bigint",
                   "27"  => "double",
                   "35"  => "timestamp",
                   "37"  => "varchar",
                   "261" => "blob");
  
  public function getTableList()
  {
    $sql  = 'SELECT RDB$RELATION_NAME FROM RDB$RELATIONS WHERE RDB$SYSTEM_FLAG = 0';
    $rows = $this->driver->execute($sql);
    if (empty($rows)) return array();
    
    $tables = array();
    foreach ($rows as $row) {
      $tables[] = trim($row['rdb$relation_name']);
    }
    
    return array_map("strtolower", $tables);
  }
  
  protected function createColumns($tblName)
  {
    $tblName = strtoupper($tblName);
    
    $sql = <<<SQL
SELECT
  rf.RDB\$FIELD_NAME, f.RDB\$FIELD_TYPE, f.RDB\$FIELD_SUB_TYPE,
  rf.RDB\$NULL_FLAG, f.RDB\$CHARACTER_LENGTH, rf.RDB\$DEFAULT_SOURCE
  FROM RDB\$FIELDS f, RDB\$RELATION_FIELDS rf
  WHERE f.RDB\$FIELD_NAME = rf.RDB\$FIELD_SOURCE
    AND rf.RDB\$RELATION_NAME = '{$tblName}'
  ORDER BY rf.RDB\$FIELD_POSITION ASC
SQL;
    
    $rows = $this->driver->execute($sql);
    if (empty($rows)) return array();
    
    $this->createGenerators();
    $this->createPrimaryKeys($tblName);
    
    $columns = array();
    foreach ($rows as $row) {
      $colName = strtolower(trim($row['rdb$field_name']));
      $columns[$colName] = $this->createColumn($row, $tblName);
    }
    
    return $columns;
  }
  
  protected function createColumn($row, $tblName)
  {
    $fieldName = trim($row['rdb$field_name']);
    
    $column = new Sabel_Db_Metadata_Column();
    $column->name = strtolower($fieldName);
    $column->nullable = ($row['rdb$null_flag'] === null);
    $type = $this->types[$row['rdb$field_type']];
    
    if ($type === "blob" && $row['rdb$field_sub_type'] === 1) {
      $column->type = Sabel_Db_Type::TEXT;
    } elseif ($type === "char" && $row['rdb$character_length'] === 1) {
      $column->type = Sabel_Db_Type::BOOL;
    } else {
      Sabel_Db_Type_Manager::create()->applyType($column, $type);
    }
    
    $seq = "{$tblName}_{$fieldName}_SEQ";
    $column->increment = (in_array($seq, $this->sequences));
    $column->primary   = (in_array($fieldName, $this->primaryKeys));
    
    if (($default = $row['rdb$default_source']) !== null) {
      $default = substr($default, 8);
      if ($default{0} === "'") {
        $default = substr($default, 1, -1);
      }
    }
    
    $this->setDefaultValue($column, $default);
    
    if ($column->isString()) {
      $column->max = (int)$row['rdb$character_length'];
    }
    
    return $column;
  }
  
  public function getForeignKeys($tblName)
  {
    $tblName = strtoupper($tblName);
    
    $sql = <<<SQL
SELECT
  seg.RDB\$FIELD_NAME AS column_name, rc2.RDB\$RELATION_NAME AS ref_table,
  seg2.RDB\$FIELD_NAME AS ref_column, refc.RDB\$DELETE_RULE, refc.RDB\$UPDATE_RULE
  FROM RDB\$RELATION_CONSTRAINTS rc
    INNER JOIN RDB\$INDEX_SEGMENTS seg
      ON rc.RDB\$INDEX_NAME = seg.RDB\$INDEX_NAME
    INNER JOIN RDB\$INDICES ind
      ON rc.RDB\$INDEX_NAME = ind.RDB\$INDEX_NAME
    INNER JOIN RDB\$RELATION_CONSTRAINTS rc2
      ON ind.RDB\$FOREIGN_KEY = rc2.RDB\$INDEX_NAME
    INNER JOIN RDB\$INDEX_SEGMENTS seg2
      ON ind.RDB\$FOREIGN_KEY = seg2.RDB\$INDEX_NAME
    INNER JOIN RDB\$REF_CONSTRAINTS refc
      ON rc2.rdb\$constraint_name = refc.RDB\$CONST_NAME_UQ
  WHERE rc.RDB\$CONSTRAINT_TYPE = 'FOREIGN KEY'
    AND rc.RDB\$RELATION_NAME = '{$tblName}'
SQL;
    
    $rows = $this->driver->execute($sql);
    if (empty($rows)) return null;
    
    $columns = array();
    foreach ($rows as $row) {
      $row = array_map("trim", $row);
      $column = strtolower($row["column_name"]);
      $columns[$column]["referenced_table"]  = strtolower($row["ref_table"]);
      $columns[$column]["referenced_column"] = strtolower($row["ref_column"]);
      $columns[$column]["on_delete"]         = $row['rdb$delete_rule'];
      $columns[$column]["on_update"]         = $row['rdb$update_rule'];
    }
    
    return $columns;
  }
  
  public function getUniques($tblName)
  {
    $tblName = strtoupper($tblName);
    
    $sql = <<<SQL
SELECT
  seg.RDB\$INDEX_NAME, seg.RDB\$FIELD_NAME
  FROM RDB\$RELATION_CONSTRAINTS rc
  INNER JOIN RDB\$INDEX_SEGMENTS seg
    ON seg.RDB\$INDEX_NAME = rc.RDB\$INDEX_NAME
  WHERE rc.RDB\$RELATION_NAME = '{$tblName}'
    AND rc.RDB\$CONSTRAINT_TYPE = 'UNIQUE'
SQL;
    
    $rows = $this->driver->execute($sql);
    if (empty($rows)) return null;
    
    $uniques = array();
    foreach ($rows as $row) {
      $row = array_map("trim", $row);
      $key = $row['rdb$index_name'];
      $uniques[$key][] = strtolower($row['rdb$field_name']);
    }
    
    return array_values($uniques);
  }
  
  private function createGenerators()
  {
    if (!empty($this->sequences)) return;
    
    $sql = 'SELECT RDB$GENERATOR_NAME FROM RDB$GENERATORS '
         . 'WHERE RDB$SYSTEM_FLAG = 0 OR RDB$SYSTEM_FLAG IS NULL';
    
    $gens =& $this->sequences;
    $rows = $this->driver->execute($sql);
    if (!$rows) return;
    
    foreach ($rows as $row) {
      $gens[] = trim($row['rdb$generator_name']);
    }
  }
  
  private function createPrimaryKeys($tblName)
  {
    if (!empty($this->primaryKeys)) return;
    
    $sql = <<<SQL
SELECT
  RDB\$FIELD_NAME
  FROM RDB\$RELATION_CONSTRAINTS rel
    INNER JOIN RDB\$INDEX_SEGMENTS seg
      ON rel.RDB\$INDEX_NAME = seg.RDB\$INDEX_NAME
  WHERE rel.RDB\$RELATION_NAME = '{$tblName}'
    AND rel.RDB\$CONSTRAINT_TYPE = 'PRIMARY KEY'
SQL;
    
    $keys =& $this->primaryKeys;
    $rows = $this->driver->execute($sql);
    if (!$rows) return;
    
    foreach ($rows as $row) {
      $keys[] = trim($row['rdb$field_name']);
    }
  }
}
