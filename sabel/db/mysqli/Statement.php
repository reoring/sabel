<?php

/**
 * Sabel_Db_Mysqli_Statement
 *
 * @category   DB
 * @package    org.sabel.db
 * @author     Ebine Yutaka <ebine.yutaka@sabel.jp>
 * @copyright  2004-2008 Mori Reo <mori.reo@sabel.jp>
 * @license    http://www.opensource.org/licenses/bsd-license.php  BSD License
 */
class Sabel_Db_Mysqli_Statement extends Sabel_Db_Mysql_Statement
{
  public function __construct(Sabel_Db_Mysqli_Driver $driver)
  {
    $this->driver = $driver;
  }
  
  public function escape(array $values)
  {
    $conn = $this->driver->getConnection();
    
    foreach ($values as &$val) {
      if (is_bool($val)) {
        $val = ($val) ? 1 : 0;
      } elseif (is_string($val)) {
        $val = "'" . mysqli_real_escape_string($conn, $val) . "'";
      }
    }
    
    return $values;
  }
  
  public function createBlob($binary)
  {
    $conn = $this->driver->getConnection();
    return new Sabel_Db_Mysqli_Blob($conn, $binary);
  }
}
