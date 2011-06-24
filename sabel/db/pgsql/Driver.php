<?php

/**
 * Driver for PostgreSQL
 *
 * @category   DB
 * @package    org.sabel.db
 * @author     Ebine Yutaka <ebine.yutaka@sabel.jp>
 * @copyright  2004-2008 Mori Reo <mori.reo@sabel.jp>
 * @license    http://www.opensource.org/licenses/bsd-license.php  BSD License
 */
class Sabel_Db_Pgsql_Driver extends Sabel_Db_Driver
{
  public function connect(array $params)
  {
    $host = $params["host"];
    $user = $params["user"];
    $pass = $params["password"];
    $dbs  = $params["database"];
    
    $host = (isset($params["port"])) ? $host . " port=" . $params["port"] : $host;
    $conn = pg_connect("host={$host} dbname={$dbs} user={$user} password={$pass}",
                       PGSQL_CONNECT_FORCE_NEW);
    
    if ($conn) {
      if (isset($params["charset"])) {
        pg_set_client_encoding($conn, $params["charset"]);
      }
      
      return $conn;
    } else {
      return "cannot connect to PostgreSQL. please check your configuration.";
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
    pg_close($connection);
    unset($this->connection);
  }
  
  public function execute($sql, $bindParams = array(), $additionalParameters = array())
  {
    $sql = $this->bind($sql, $bindParams);
    
    if ($result = pg_query($this->connection, $sql)) {
      $rows = pg_fetch_all($result);
      $this->affectedRows = pg_affected_rows($result);
      pg_free_result($result);
      return (empty($rows)) ? null : $rows;
    } else {
      $this->executeError($sql);
    }
  }
  
  public function getLastInsertId()
  {
    $rows = $this->execute("SELECT LASTVAL() AS id");
    return $rows[0]["id"];
  }
  
  private function executeError($sql)
  {
    $error = pg_last_error($this->connection);
    throw new Sabel_Db_Exception_Driver("{$error}, SQL: $sql");
  }
}
