<?php

/**
 * Sabel_Db_Mysqli_Blob
 *
 * @category   DB
 * @package    org.sabel.db
 * @author     Ebine Yutaka <ebine.yutaka@sabel.jp>
 * @copyright  2004-2008 Mori Reo <mori.reo@sabel.jp>
 * @license    http://www.opensource.org/licenses/bsd-license.php  BSD License
 */
class Sabel_Db_Mysqli_Blob extends Sabel_Db_Mysql_Blob
{
  public function getData()
  {
    return "'" . mysqli_real_escape_string($this->conn, $this->binary) . "'";
  }
}
