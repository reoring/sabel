<?php

/**
 * Driver for PDO_OCI
 *
 * @category   DB
 * @package    org.sabel.db.pdo
 * @author     Ebine Yutaka <ebine.yutaka@sabel.jp>
 * @copyright  2004-2008 Mori Reo <mori.reo@sabel.jp>
 * @license    http://www.opensource.org/licenses/bsd-license.php  BSD License
 */
class Sabel_Db_Pdo_Oci_Driver extends Sabel_Db_Pdo_Driver
{
  private $lastInsertId = null;
  
  public function connect(array $params)
  {
    try {
      $dsn = "oci:dbname=//{$params["host"]}";
      if (isset($params["port"])) $dsn .= ":port={$params["port"]}";
      $dsn .= "/" . $params["database"];
      if (isset($params["charset"])) $dsn .= ";charset={$params["charset"]}";
      
      $conn = new PDO($dsn, $params["user"], $params["password"]);
      $pdoStmt = $conn->prepare("ALTER SESSION SET NLS_DATE_FORMAT = 'YYYY-MM-DD HH24:MI:SS'");
      $pdoStmt->execute();
      
      return $conn;
    } catch (PDOException $e) {
      return $e->getMessage();
    }
  }
  
  public function execute($sql, $bindParams = array(), $additionalParameters = array())
  {
    if (($result = parent::execute($sql, $bindParams)) === null) {
      return null;
    } else {
      return array_map("array_change_key_case", $result);
    }
  }
  
  public function setLastInsertId($id)
  {
    $this->lastInsertId = $id;
  }
  
  public function getLastInsertId()
  {
    return $this->lastInsertId;
  }
  
  public function setTransactionIsolationLevel($level)
  {
    switch ($level) {
      case self::TRANS_READ_UNCOMMITTED:
      case self::TRANS_READ_COMMITTED:
        $query = "SET TRANSACTION ISOLATION LEVEL READ COMMITTED";
        break;
      case self::TRANS_REPEATABLE_READ:
      case self::TRANS_SERIALIZABLE:
        $query = "SET TRANSACTION ISOLATION LEVEL SERIALIZABLE";
        break;
      default:
        throw new Sabel_Exception_InvalidArgument("invalid isolation level.");
    }
    
    $this->execute($query);
  }
}
