<?php

/**
 * Sabel_Db_Pdo_Statement
 *
 * @abstract
 * @category   DB
 * @package    org.sabel.db.pdo
 * @author     Ebine Yutaka <ebine.yutaka@sabel.jp>
 * @copyright  2004-2008 Mori Reo <mori.reo@sabel.jp>
 * @license    http://www.opensource.org/licenses/bsd-license.php  BSD License
 */
abstract class Sabel_Db_Pdo_Statement extends Sabel_Db_Statement
{
  abstract public function escape(array $values);
  
  public function __construct(Sabel_Db_Pdo_Driver $driver)
  {
    $this->driver = $driver;
  }
  
  public function bind($key, $val)
  {
    $this->bindValues[":{$key}"] = $val;
    
    return $this;
  }
  
  public function unbind($key)
  {
    if (is_array($key)) {
      foreach ($key as $k) {
        unset($this->bindValues[":{$k}"]);
      }
    } else {
      unset($this->bindValues[":{$key}"]);
    }
    
    return $this;
  }
  
  public function values(array $values)
  {
    $columns = $this->metadata->getColumns();
    
    foreach ($values as $k => &$v) {
      if (isset($columns[$k]) && $columns[$k]->isBinary()) {
        $v = $this->createBlob($v);
      }
    }
    
    $this->values = $values;
    $this->binds($values);
    
    return $this;
  }
  
  public function execute($bindValues = array(), $additionalParameters = array(), $query = null)
  {
    $query = preg_replace('/@([a-zA-Z0-9_]+)@/', ':$1', $this->getQuery());
    return parent::execute($bindValues, $additionalParameters, $query);
  }
  
  public function createBlob($binary)
  {
    return new Sabel_Db_Pdo_Blob($binary);
  }
}
