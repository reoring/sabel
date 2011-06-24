<?php

/**
 * Driver for MySQL
 *
 * @category   DB
 * @package    org.sabel.db
 * @author     Ebine Yutaka <ebine.yutaka@sabel.jp>
 * @copyright  2004-2008 Mori Reo <mori.reo@sabel.jp>
 * @license    http://www.opensource.org/licenses/bsd-license.php  BSD License
 */
class Sabel_Db_Mysql_Driver extends Sabel_Db_Driver
{
  public function connect(array $params)
  {
    $host = $params["host"];
    $host = (isset($params["port"])) ? $host . ":" . $params["port"] : $host;
    $conn = mysql_connect($host, $params["user"], $params["password"], true);
    
    if ($conn) {
      if (!mysql_select_db($params["database"], $conn)) {
        return mysql_error();
      }
      
      if (isset($params["charset"])) {
        if (function_exists("mysql_set_charset")) {
          mysql_set_charset($params["charset"], $conn);
        } else {
          mysql_query("SET NAMES " . $params["charset"], $conn);
        }
      }
      
      return $conn;
    } else {
      return mysql_error();
    }
  }
  
  public function begin($isolationLevel = null)
  {
    if ($isolationLevel !== null) {
      $this->setTransactionIsolationLevel($isolationLevel);
    }
    
    $this->execute("START TRANSACTION");
    $this->autoCommit = false;
    return $this->connection;
  }
  
  public function commit()
  {
    $this->execute("COMMIT");
    $this->autoCommit = true;
  }
  
  public function rollback()
  {
    $this->execute("ROLLBACK");
    $this->autoCommit = true;
  }
  
  public function close($connection)
  {
    mysql_close($connection);
    unset($this->connection);
  }
  
  public function execute($sql, $bindParams = array(), $additionalParameters = array())
  {
    $sql = $this->bind($sql, $bindParams);
    $result = mysql_query($sql, $this->connection);
    if (!$result) $this->executeError($sql);
    
    $rows = array();
    if (is_resource($result)) {
      while ($row = mysql_fetch_assoc($result)) $rows[] = $row;
      mysql_free_result($result);
      $this->affectedRows = 0;
    } else {
      $this->affectedRows = mysql_affected_rows($this->connection);
    }
    
    return (empty($rows)) ? null : $rows;
  }
  
  public function getLastInsertId()
  {
    return mysql_insert_id($this->connection);
  }
  
  private function executeError($sql)
  {
    $error = mysql_error($this->connection);
    throw new Sabel_Db_Exception_Driver("{$error}, SQL: $sql");
  }
}
