<?php

/**
 * Driver for PDO_PGSQL
 *
 * @category   DB
 * @package    org.sabel.db.pdo
 * @author     Ebine Yutaka <ebine.yutaka@sabel.jp>
 * @copyright  2004-2008 Mori Reo <mori.reo@sabel.jp>
 * @license    http://www.opensource.org/licenses/bsd-license.php  BSD License
 */
class Sabel_Db_Pdo_Pgsql_Driver extends Sabel_Db_Pdo_Driver
{
  public function connect(array $params)
  {
    try {
      $dsn = "pgsql:host={$params["host"]};dbname={$params["database"]}";
      if (isset($params["port"])) $dsn .= ";port={$params["port"]}";
      $conn = new PDO($dsn, $params["user"], $params["password"]);
      
      if (isset($params["charset"])) {
        $conn->exec("SET NAMES " . $params["charset"]);
      }
      
      return $conn;
    } catch (PDOException $e) {
      return $e->getMessage();
    }
  }
  
  public function getLastInsertId()
  {
    $rows = $this->execute("SELECT LASTVAL() AS id");
    return $rows[0]["id"];
  }
}
