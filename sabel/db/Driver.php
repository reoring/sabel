<?php

/**
 * Sabel_Db_Driver
 *
 * @abstract
 * @category   DB
 * @package    org.sabel.db
 * @author     Ebine Yutaka <ebine.yutaka@sabel.jp>
 * @copyright  2004-2008 Mori Reo <mori.reo@sabel.jp>
 * @license    http://www.opensource.org/licenses/bsd-license.php  BSD License
 */
abstract class Sabel_Db_Driver extends Sabel_Object
{
  const TRANS_READ_UNCOMMITTED = 1;
  const TRANS_READ_COMMITTED   = 2;
  const TRANS_REPEATABLE_READ  = 3;
  const TRANS_SERIALIZABLE     = 4;
  
  /**
   * @var string
   */
  protected $connectionName = "";
  
  /**
   * @var boolean
   */
  protected $autoCommit = true;
  
  /**
   * @var resource
   */
  protected $connection = null;
  
  /**
   * @var int
   */
  protected $affectedRows = 0;
  
  abstract public function connect(array $params);
  abstract public function begin($isolationLevel = null);
  abstract public function commit();
  abstract public function rollback();
  abstract public function execute($sql, $bindParams = array(), $additionalParameters = array());
  abstract public function getLastInsertId();
  abstract public function close($connection);
  
  public function __construct($connectionName)
  {
    $this->connectionName = $connectionName;
  }
  
  public function getConnectionName()
  {
    return $this->connectionName;
  }
  
  public function setConnection($connection)
  {
    $this->connection = $connection;
  }
  
  public function getConnection()
  {
    return $this->connection;
  }
  
  public function autoCommit($bool)
  {
    $this->autoCommit = $bool;
  }
  
  public function getAffectedRows()
  {
    return $this->affectedRows;
  }
  
  public function setTransactionIsolationLevel($level)
  {
    switch ($level) {
      case self::TRANS_READ_UNCOMMITTED:
        $query = "SET TRANSACTION ISOLATION LEVEL READ UNCOMMITTED";
        break;
      case self::TRANS_READ_COMMITTED:
        $query = "SET TRANSACTION ISOLATION LEVEL READ COMMITTED";
        break;
      case self::TRANS_REPEATABLE_READ:
        $query = "SET TRANSACTION ISOLATION LEVEL REPEATABLE READ";
        break;
      case self::TRANS_SERIALIZABLE:
        $query = "SET TRANSACTION ISOLATION LEVEL SERIALIZABLE";
        break;
      default:
        throw new Sabel_Exception_InvalidArgument("invalid isolation level.");
    }
    
    $this->execute($query);
  }
  
  protected function bind($sql, $bindParam)
  {
    if (empty($bindParam)) return $sql;
    
    if (in_array(null, $bindParam, true)) {
      array_walk($bindParam, create_function('&$val', 'if ($val === null) $val = "NULL";'));
    }
    
    return strtr($sql, $bindParam);
  }
}
