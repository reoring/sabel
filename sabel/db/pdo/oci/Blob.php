<?php

/**
 * Sabel_Db_Pdo_Oci_Blob
 *
 * @category   DB
 * @package    org.sabel.db
 * @author     Ebine Yutaka <ebine.yutaka@sabel.jp>
 * @copyright  2004-2008 Mori Reo <mori.reo@sabel.jp>
 * @license    http://www.opensource.org/licenses/bsd-license.php  BSD License
 */
class Sabel_Db_Pdo_Oci_Blob extends Sabel_Db_Pdo_Blob
{
  protected $filePath = "";
  
  public function __construct($binary)
  {
    $this->binary = $binary;
    $this->filePath = get_temp_dir() . DS . "sbl_" . md5hash();
  }
  
  public function getData()
  {
    file_put_contents($this->filePath, $this->binary);
    return fopen($this->filePath, "rb");
  }
  
  public function unlink()
  {
    if (is_file($this->filePath)) {
      unlink($this->filePath);
    }
  }
}
