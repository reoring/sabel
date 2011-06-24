<?php

/**
 * @category   KVS
 * @package    org.sabel.kvs
 * @author     Ebine Yutaka <ebine.yutaka@sabel.php-framework.org>
 * @copyright  2004-2008 Mori Reo <mori.reo@sabel.jp>
 * @license    http://www.opensource.org/licenses/bsd-license.php  BSD License
 */
class Sabel_Kvs_Database implements Sabel_Kvs_Interface
{
  private static $instances = array();
  
  /**
   * @var Sabel_Db_Model
   */
  protected $model = "";
  
  private function __construct($mdlName)
  {
    $this->model = MODEL($mdlName);
  }
  
  public static function create($mdlName = "SblKvs")
  {
    if (isset(self::$instances[$mdlName])) {
      return self::$instances[$mdlName];
    }
    
    return self::$instances[$mdlName] = new self($mdlName);
  }
  
  public function read($key)
  {
    $result = null;
    
    Sabel_Db_Transaction::activate();
    
    try {
      if ($model = $this->fetch($key, true)) {
        if (($timeout = (int)$model->timeout) === 0) {
          $result = $model->value;
        } else {
          if ($timeout <= time()) {
            $model->delete();
          } else {
            $result = $model->value;
          }
        }
        
        if ($result !== null) {
          $result = unserialize(str_replace("\\000", "\000", $result));
        }
      }
      
      Sabel_Db_Transaction::commit();
    } catch (Exception $e) {
      Sabel_Db_Transaction::rollback();
      throw $e;
    }
    
    return ($result === false) ? null : $result;
  }
  
  public function write($key, $value, $timeout = 0)
  {
    Sabel_Db_Transaction::activate();
    
    try {
      if ($timeout !== 0) {
        $timeout = time() + $timeout;
      }
      
      $value = str_replace("\000", "\\000", serialize($value));
      
      if ($model = $this->fetch($key, true)) {
        $model->save(array(
          "value"   => $value,
          "timeout" => $timeout
        ));
      } else {
        $this->model->insert(array(
          "key"     => $key,
          "value"   => $value,
          "timeout" => $timeout,
        ));
      }
      
      Sabel_Db_Transaction::commit();
    } catch (Exception $e) {
      Sabel_Db_Transaction::rollback();
      throw $e;
    }
  }
  
  public function delete($key)
  {
    $result = null;
    
    Sabel_Db_Transaction::activate();
    
    try {
      if ($model = $this->fetch($key, true)) {
        if (($timeout = (int)$model->timeout) !== 0) {
          if ($timeout > time()) {
            $result = unserialize(str_replace("\\000", "\000", $model->value));
          }
        }
        
        $model->delete();
      }
      
      Sabel_Db_Transaction::commit();
    } catch (Exception $e) {
      Sabel_Db_Transaction::rollback();
      throw $e;
    }
    
    return $result;
  }
  
  protected function fetch($key, $forUpdate = false)
  {
    if ($forUpdate) {
      $results = $this->model->selectForUpdate($key);
      return (isset($results[0])) ? $results[0] : null;
    } else {
      $model = $this->model->selectOne($key);
      return ($model->isSelected()) ? $model : null;
    }
  }
}
