<?php

/**
 * Abstract Driver for PDO
 *
 * @abstract
 * @category   DB
 * @package    org.sabel.db.pdo
 * @author     Ebine Yutaka <ebine.yutaka@sabel.jp>
 * @copyright  2004-2008 Mori Reo <mori.reo@sabel.jp>
 * @license    http://www.opensource.org/licenses/bsd-license.php  BSD License
 */
abstract class Sabel_Db_Pdo_Driver extends Sabel_Db_Driver
{
  public function begin($isolationLevel = null)
  {
    if ($isolationLevel !== null) {
      $this->setTransactionIsolationLevel($isolationLevel);
    }
    
    try {
      $this->connection->beginTransaction();
      $this->autoCommit = false;
      return $this->connection;
    } catch (PDOException $e) {
      throw new Sabel_Db_Exception_Driver($e->getMessage());
    }
  }
  
  public function commit()
  {
    try {
      $this->connection->commit();
      $this->autoCommit = true;
    } catch (PDOException $e) {
      throw new Sabel_Db_Exception_Driver($e->getMessage());
    }
  }
  
  public function rollback()
  {
    try {
      $this->connection->rollback();
      $this->autoCommit = true;
    } catch (PDOException $e) {
      throw new Sabel_Db_Exception_Driver($e->getMessage());
    }
  }
  
  public function close($connection)
  {
    unset($connection);
    unset($this->connection);
  }
  
  public function execute($sql, $bindParams = array(), $additionalParameters = array())
  {
    $connection = $this->connection;
    if (!($pdoStmt = $connection->prepare($sql))) {
      $error = $connection->errorInfo();
      throw new Sabel_Db_Exception_Driver("PdoStatement is invalid. {$error[2]}");
    }
    
    $hasBlob = false;
    foreach ($bindParams as $name => $value) {
      if ($value instanceof Sabel_Db_Pdo_Blob) {
        $hasBlob = true;
        $pdoStmt->bindValue($name, $value->getData(), PDO::PARAM_LOB);
      } else {
        $pdoStmt->bindValue($name, $value);
      }
    }
    
    if ($hasBlob && $this->autoCommit) {
      $connection->beginTransaction();
    }
    
    if (!$result = $pdoStmt->execute()) {
      $this->executeError($connection, $pdoStmt, $bindParams);
    }
    
    $rows = $pdoStmt->fetchAll(PDO::FETCH_ASSOC);
    $this->affectedRows = $pdoStmt->rowCount();
    $pdoStmt->closeCursor();
    
    if ($hasBlob && $this->autoCommit) {
      $connection->commit();
    }
    
    return (empty($rows)) ? null : $rows;
  }
  
  private function executeError($conn, $pdoStmt, $bindParams)
  {
    if (is_object($pdoStmt)) {
      $error = $pdoStmt->errorInfo();
      $sql   = $pdoStmt->queryString;
    } else {
      $error = $conn->errorInfo();
      $sql   = null;
    }
    
    $error = (isset($error[2])) ? $error[2] : print_r($error, true);
    if ($sql !== null) $error .= ", SQL: $sql";
    
    if (!empty($bindParams)) {
      $error .= PHP_EOL . "BIND_PARAMS: " . print_r($bindParams, true);
    }
    
    throw new Sabel_Db_Exception_Driver($error);
  }
}
