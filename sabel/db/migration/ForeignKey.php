<?php

/**
 * Sabel_Db_Migration_ForeignKey
 *
 * @category   DB
 * @package    org.sabel.db
 * @author     Ebine Yutaka <ebine.yutaka@sabel.jp>
 * @copyright  2004-2008 Mori Reo <mori.reo@sabel.jp>
 * @license    http://www.opensource.org/licenses/bsd-license.php  BSD License
 */
class Sabel_Db_Migration_ForeignKey
{
  public
    $column    = null,
    $refTable  = null,
    $refColumn = null,
    $onDelete  = null,
    $onUpdate  = null;

  public function __construct($column)
  {
    $this->column = $column;
  }

  public function get()
  {
    if ($this->refTable === null && $this->refColumn === null) {
      $table  = substr($this->column, 0, -3);
      $column = str_replace($table, "", $this->column);
      if ($column === "_id") {
        $column = "id";
      } else {
        throw new Sabel_Db_Exception("invalid column name for foreign key.");
      }

      $this->refTable  = $table;
      $this->refColumn = $column;
    }

    return $this;
  }

  public function table($tblName)
  {
    $this->refTable = $tblName;

    return $this;
  }

  public function column($colName)
  {
    $this->refColumn = $colName;

    return $this;
  }

  public function onDelete($arg)
  {
    $this->onDelete = $arg;

    return $this;
  }

  public function onUpdate($arg)
  {
    $this->onUpdate = $arg;

    return $this;
  }
}
