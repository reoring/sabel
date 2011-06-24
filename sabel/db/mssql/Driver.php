<?php

/**
 * Driver for Microsoft SQL Server 2005, 2008
 *
 * @category   DB
 * @package    org.sabel.db
 * @author     Ebine Yutaka <ebine.yutaka@sabel.jp>
 * @copyright  2004-2008 Mori Reo <mori.reo@sabel.jp>
 * @license    http://www.opensource.org/licenses/bsd-license.php  BSD License
 */
class Sabel_Db_Mssql_Driver extends Sabel_Db_Driver
{
  public function connect(array $params)
  {
    $host = $params["host"];
    $host = (isset($params["port"])) ? $host . "," . $params["port"] : $host;
    $conn = mssql_connect($host, $params["user"], $params["password"], true);
    
    if ($conn) {
      mssql_select_db($params["database"], $conn);
      return $conn;
    } else {
      return mssql_get_last_message();
    }
  }
  
  public function begin($isolationLevel = null)
  {
    if ($isolationLevel !== null) {
      $this->setTransactionIsolationLevel($isolationLevel);
    }
    
    $this->execute("BEGIN TRANSACTION");
    $this->autoCommit = false;
    return $this->connection;
  }
  
  public function commit()
  {
    $this->execute("COMMIT TRANSACTION");
    $this->autoCommit = true;
  }
  
  public function rollback()
  {
    $this->execute("ROLLBACK TRANSACTION");
    $this->autoCommit = true;
  }
  
  public function close($connection)
  {
    mssql_close($connection);
    unset($this->connection);
  }
  
  public function execute($sql, $bindParams = array(), $additionalParameters = array())
  {
    $sql = $this->bind($sql, $bindParams);
    
    if ($result = mssql_query($sql, $this->connection)) {
      $rows = array();
      if (is_resource($result)) {
        while ($row = mssql_fetch_assoc($result)) $rows[] = $row;
        mssql_free_result($result);
        $this->affectedRows = 0;
      } else {
        $this->affectedRows = mssql_rows_affected($this->connection);
      }
      
      return (empty($rows)) ? null : $rows;
    } else {
      $this->executeError($sql);
    }
  }
  
  public function getLastInsertId()
  {
    $rows = $this->execute("SELECT SCOPE_IDENTITY() AS id");
    return $rows[0]["id"];
  }
  
  private function executeError($sql)
  {
    $error = mssql_get_last_message();
    throw new Sabel_Db_Exception_Driver("{$error}, SQL: $sql");
  }
}
