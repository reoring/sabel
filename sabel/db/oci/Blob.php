<?php

/**
 * Sabel_Db_Oci_Blob
 *
 * @category   DB
 * @package    org.sabel.db
 * @author     Ebine Yutaka <ebine.yutaka@sabel.jp>
 * @copyright  2004-2008 Mori Reo <mori.reo@sabel.jp>
 * @license    http://www.opensource.org/licenses/bsd-license.php  BSD License
 */
class Sabel_Db_Oci_Blob extends Sabel_Db_Abstract_Blob
{
  protected $conn = null;
  protected $lob  = null;
  
  public function __construct($conn, $binary)
  {
    $this->conn   = $conn;
    $this->binary = $binary;
    $this->lob    = oci_new_descriptor($conn, OCI_D_LOB);
  }
  
  public function getData()
  {
    return $this->binary;
  }
  
  public function getLob()
  {
    return $this->lob;
  }
  
  public function save()
  {
    $this->lob->save($this->getData());
  }
}
