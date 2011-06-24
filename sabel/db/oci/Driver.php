<?php

/**
 * Driver for Oracle
 *
 * @category   DB
 * @package    org.sabel.db
 * @author     Ebine Yutaka <ebine.yutaka@sabel.jp>
 * @copyright  2004-2008 Mori Reo <mori.reo@sabel.jp>
 * @license    http://www.opensource.org/licenses/bsd-license.php  BSD License
 */
class Sabel_Db_Oci_Driver extends Sabel_Db_Driver
{
  /**
   * @var int
   */
  private $limit = null;
  
  /**
   * @var int
   */
  private $offset = null;
  
  /**
   * @var int
   */
  private $lastInsertId = null;
  
  public function connect(array $params)
  {
    $database = "//" . $params["host"] . "/" . $params["database"];
    $encoding = (isset($params["charset"])) ? $params["charset"] : null;
    
    $conn = oci_new_connect($params["user"], $params["password"], $database, $encoding);
    
    if ($conn) {
      $stmt = oci_parse($conn, "ALTER SESSION SET NLS_DATE_FORMAT = 'YYYY-MM-DD HH24:MI:SS'");
      oci_execute($stmt, OCI_COMMIT_ON_SUCCESS);
      return $conn;
    } else {
      $e = oci_error();
      return $e["message"];
    }
  }
  
  
  public function begin($isolationLevel = null)
  {
    if ($isolationLevel !== null) {
      $this->setTransactionIsolationLevel($isolationLevel);
    }
    
    $this->autoCommit = false;
    return $this->connection;
  }
  
  public function commit()
  {
    if (oci_commit($this->connection)) {
      $this->autoCommit = true;
    } else {
      $e = oci_error($this->connection);
      throw new Sabel_Db_Exception_Driver($e["message"]);
    }
  }
  
  public function rollback()
  {
    if (oci_rollback($this->connection)) {
      $this->autoCommit = true;
    } else {
      $e = oci_error($this->connection);
      throw new Sabel_Db_Exception_Driver($e["message"]);
    }
  }
  
  public function close($connection)
  {
    oci_close($connection);
    unset($this->connection);
  }
  
  public function setLimit($limit)
  {
    $this->limit = $limit;
  }
  
  public function setOffset($offset)
  {
    $this->offset = $offset;
  }
  
  public function setLastInsertId($id)
  {
    $this->lastInsertId = $id;
  }
  
  public function execute($sql, $bindParams = array(), $additionalParameters = array())
  {
    $connection = $this->connection;
    $sql = $this->bind($sql, $bindParams);
    
    // array $blobs Sabel_Db_Oci_Blob[]
    $blobs = (isset($additionalParameters["blob"])) ? $additionalParameters["blob"] : array();
    
    if (empty($blobs)) {
      $execMode = ($this->autoCommit) ? OCI_COMMIT_ON_SUCCESS : OCI_DEFAULT;
      $ociStmt  = oci_parse($connection, $sql);
      $result   = oci_execute($ociStmt, $execMode);
    } else {
      $ociStmt = oci_parse($connection, $sql);
      foreach ($blobs as $column => $blob) {
        $lob = $blob->getLob();
        oci_bind_by_name($ociStmt, ":" . $column, $lob, -1, SQLT_BLOB);
      }
      
      $result = oci_execute($ociStmt, OCI_DEFAULT);
      foreach ($blobs as $blob) $blob->save();
    }
    
    if (!$result) $this->executeError($ociStmt);
    
    $rows = array();
    if (oci_statement_type($ociStmt) === "SELECT") {
      oci_fetch_all($ociStmt, $rows, $this->offset, $this->limit, OCI_ASSOC|OCI_FETCHSTATEMENT_BY_ROW);
      $rows = array_map("array_change_key_case", $rows);
    } else {
      $this->affectedRows = oci_num_rows($ociStmt);
    }
    
    if (!empty($blobs) && $this->autoCommit) {
      $this->commit();
    }
    
    oci_free_statement($ociStmt);
    return (empty($rows)) ? null : $rows;
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
  
  private function executeError($ociStmt)
  {
    $e = oci_error($ociStmt);
    throw new Sabel_Db_Exception_Driver($e["message"] . ", SQL:" . $e["sqltext"]);
  }
}
