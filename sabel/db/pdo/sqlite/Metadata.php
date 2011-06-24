<?php

/**
 * Sabel_Db_Pdo_Sqlite_Metadata
 *
 * @category   DB
 * @package    org.sabel.db.pdo
 * @author     Ebine Yutaka <ebine.yutaka@sabel.jp>
 * @copyright  2004-2008 Mori Reo <mori.reo@sabel.jp>
 * @license    http://www.opensource.org/licenses/bsd-license.php  BSD License
 */
class Sabel_Db_Pdo_Sqlite_Metadata extends Sabel_Db_Abstract_Metadata
{
  public function getTableList()
  {
    $sql  = "SELECT name FROM sqlite_master WHERE type = 'table'";
    $rows = $this->driver->execute($sql);
    if (empty($rows)) return array();
    
    $tables = array();
    foreach ($rows as $row) $tables[] = $row["name"];
    return $tables;
  }
  
  public function getForeignKeys($tblName)
  {
    return null;
  }
  
  public function getUniques($tblName)
  {
    $createSql = $this->getCreateSql($tblName);
    preg_match_all("/UNIQUE ?(\(([^)]+)\))/i", $createSql, $matches);
    if (empty($matches[1])) return null;
    
    $uniques = array();
    foreach ($matches[2] as $unique) {
      $unique = str_replace(array('"'), "", $unique);
      $exp = array_map("trim", explode(",", $unique));
      $uniques[] = $exp;
    }
    
    return $uniques;
  }
  
  protected function createColumns($tblName)
  {
    $rows = $this->driver->execute("PRAGMA table_info('{$tblName}')");
    if (!$rows) return array();
    
    $columns = array();
    foreach ($rows as $row) {
      $co = new Sabel_Db_Metadata_Column();
      $co->name = $row["name"];
      
      if ($row["pk"] === "1") {
        $co->primary   = true;
        $co->nullable  = false;
        $co->increment = ($row["type"] === "integer");
      } else {
        $co->primary   = false;
        $co->nullable  = ($row["notnull"] === "0");
        $co->increment = false;
      }
      
      if ($this->isBoolean($row["type"])) {
        $co->type = Sabel_Db_Type::BOOL;
      } elseif (!$this->isString($co, $row["type"])) {
        Sabel_Db_Type_Manager::create()->applyType($co, $row["type"]);
      }
      
      $this->setDefaultValue($co, $row["dflt_value"]);
      if (is_string($co->default) && ($length = strlen($co->default)) > 1) {
        if ($co->default{0} === "'" && substr($co->default, --$length, 1) === "'") {
          $co->default = substr($co->default, 1, --$length);
        }
      }
      
      $columns[$co->name] = $co;
    }
    
    return $columns;
  }
  
  protected function isBoolean($type)
  {
    return ($type === "boolean" || $type === "bool");
  }
  
  protected function isString($co, $type)
  {
    $types = array("varchar", "char", "character");
    
    foreach ($types as $sType) {
      if (strpos($type, $sType) !== false) {
        $length   = strpbrk($type, "(");
        $co->type = Sabel_Db_Type::STRING;
        $co->max  = ($length === false) ? 255 : (int)substr($length, 1, -1);
        return true;
      }
    }
    
    return false;
  }
  
  private function getCreateSql($tblName)
  {
    $sql  = "SELECT sql FROM sqlite_master WHERE name = '{$tblName}'";
    $rows = $this->driver->execute($sql);
    return $rows[0]["sql"];
  }
}
