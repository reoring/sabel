<?php

/**
 * Sabel_Db_Pgsql_Blob
 *
 * @category   DB
 * @package    org.sabel.db
 * @author     Ebine Yutaka <ebine.yutaka@sabel.jp>
 * @copyright  2004-2008 Mori Reo <mori.reo@sabel.jp>
 * @license    http://www.opensource.org/licenses/bsd-license.php  BSD License
 */
class Sabel_Db_Pgsql_Blob extends Sabel_Db_Abstract_Blob
{
  protected $conn = null;
  
  public function __construct($conn, $binary)
  {
    $this->conn   = $conn;
    $this->binary = $binary;
  }
  
  public function getData()
  {
    return "'" . pg_escape_bytea($this->conn, $this->binary) . "'";
  }
}
