<?php

/**
 * Sabel_Db_Migration_AddColumn
 *
 * @category   DB
 * @package    org.sabel.db
 * @author     Ebine Yutaka <ebine.yutaka@sabel.jp>
 * @copyright  2004-2008 Mori Reo <mori.reo@sabel.jp>
 * @license    http://www.opensource.org/licenses/bsd-license.php  BSD License
 */
class Sabel_Db_Migration_AddColumn
{
  private
    $mcolumns = array(),
    $columns  = array();

  public function column($name)
  {
    $column = new Sabel_Db_Migration_Column($name);
    return $this->mcolumns[$name] = $column;
  }

  public function build()
  {
    $columns = array();

    foreach ($this->mcolumns as $column) {
      $columns[] = $column->arrange()->getColumn();
    }

    $this->columns = $columns;

    return $this;
  }

  public function getColumns()
  {
    return $this->columns;
  }
}
