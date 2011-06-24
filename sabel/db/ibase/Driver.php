<?php

/**
 * Driver for Firebird
 *
 * @category   DB
 * @package    org.sabel.db
 * @author     Ebine Yutaka <ebine.yutaka@sabel.jp>
 * @copyright  2004-2008 Mori Reo <mori.reo@sabel.jp>
 * @license    http://www.opensource.org/licenses/bsd-license.php  BSD License
 */
class Sabel_Db_Ibase_Driver extends Sabel_Db_Driver
{
  private $lastInsertId   = null;
  private $isolationLevel = 0;
  
  public function connect(array $params)
  {
    $host = $params["host"]. ":" . $params["database"];
    $enc  = (isset($params["charset"])) ? $params["charset"] : null;
    $conn = ibase_connect($host, $params["user"], $params["password"], $enc);
    
    return ($conn) ? $conn : ibase_errmsg();
  }
  
  public function begin($isolationLevel = null)
  {
    if ($isolationLevel === null) {
      $this->isolationLevel = IBASE_WRITE|IBASE_COMMITTED|IBASE_REC_NO_VERSION|IBASE_WAIT;
    } else {
      $this->setTransactionIsolationLevel($isolationLevel);
    }
    
    $this->autoCommit = false;
    $this->connection = ibase_trans($this->isolationLevel, $this->connection);
    return $this->connection;
  }
  
  public function commit()
  {
    if (ibase_commit($this->connection)) {
      $this->autoCommit = true;
    } else {
      throw new Sabel_Db_Exception_Driver(ibase_errmsg());
    }
  }
  
  public function rollback()
  {
    if (ibase_rollback($this->connection)) {
      $this->autoCommit = true;
    } else {
      throw new Sabel_Db_Exception_Driver(ibase_errmsg());
    }
  }
  
  public function close($connection)
  {
    ibase_close($connection);
    unset($this->connection);
  }
  
  public function setLastInsertId($id)
  {
    $this->lastInsertId = $id;
  }
  
  public function execute($sql, $bindParams = array(), $additionalParameters = array())
  {
    $connection = $this->connection;
    
    if (empty($bindParams)) {
      $result = ibase_query($connection, $sql);
    } else {
      $holderRegex = "/@[a-zA-Z0-9_]+@/";
      preg_match_all($holderRegex, $sql, $matches);
      $args = array($connection, preg_replace($holderRegex, "?", $sql));
      foreach ($matches[0] as $holder) {
        $args[] = $bindParams[$holder];
      }
      
      $result = call_user_func_array("ibase_query", $args);
    }
    
    if (!$result) $this->executeError($sql);
    
    $rows = array();
    if (is_resource($result)) {
      while ($row = ibase_fetch_assoc($result, IBASE_TEXT)) {
        $rows[] = array_change_key_case($row);
      }
      ibase_free_result($result);
    } else {
      $this->affectedRows = ($result === true) ? 0 : $result;
    }
    
    if ($this->autoCommit) ibase_commit($connection);
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
        $this->isolationLevel = IBASE_WRITE|IBASE_COMMITTED|IBASE_REC_VERSION|IBASE_WAIT;
        break;
      case self::TRANS_READ_COMMITTED:
        $this->isolationLevel = IBASE_WRITE|IBASE_COMMITTED|IBASE_REC_NO_VERSION|IBASE_WAIT;
        break;
      case self::TRANS_REPEATABLE_READ:
        $this->isolationLevel = IBASE_WRITE|IBASE_CONCURRENCY|IBASE_WAIT;
        break;
      case self::TRANS_SERIALIZABLE:
        $this->isolationLevel = IBASE_WRITE|IBASE_CONSISTENCY|IBASE_WAIT;
        break;
      default:
        $message = __METHOD__ . "() invalid isolation level.";
        throw new Sabel_Exception_InvalidArgument($message);
    }
  }
  
  private function executeError($sql)
  {
    throw new Sabel_Db_Exception_Driver(ibase_errmsg() . ", SQL: " . $sql);
  }
}
