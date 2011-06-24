<?php

/**
 * Sabel_Db_Model_CascadeDelete
 *
 * @category   DB
 * @package    org.sabel.db
 * @author     Ebine Yutaka <ebine.yutaka@gmail.com>
 * @copyright  2004-2008 Mori Reo <mori.reo@gmail.com>
 * @license    http://www.opensource.org/licenses/bsd-license.php  BSD License
 */
class Sabel_Db_Model_CascadeDelete
{
  protected $model = null;
  protected $keys  = array();

  protected $cascadeStack = array();

  public function __construct($mdlName, $id)
  {
    $this->model = MODEL($mdlName, $id);
  }

  public function execute($config)
  {
    if (!is_object($config)) {
      throw new Sabel_Db_Exception("argument should be an object of cascade delete config.");
    }

    $model      = $this->model;
    $cascade    = $config->getChain();
    $this->keys = $config->getKeys();
    $mdlName    = $model->getName();

    Sabel_Db_Transaction::begin($model->getConnectionName());

    $models  = array();
    $pKey    = $model->getPrimaryKey();
    $idValue = $model->$pKey;

    $childNames = $cascade[$mdlName];

    foreach ($childNames as $name) {
      $keys    = $this->getKeys($mdlName, $name, $pKey);
      $results = $this->pushStack($name, $keys["fkey"], $idValue);
      if ($results) $models[] = $results;
    }

    foreach ($models as $children) {
      $this->makeChainModels($children, $cascade);
    }

    $this->clearCascadeStack();

    $model->delete();
    Sabel_Db_Transaction::commit();
  }

  private function makeChainModels($children, &$cascade)
  {
    $childObj = $children[0];
    $mdlName  = $childObj->getName();
    if (!isset($cascade[$mdlName])) return;

    $models = array();
    $pKey   = $childObj->getPrimaryKey();
    $childNames = $cascade[$mdlName];

    foreach ($childNames as $name) {
      $keys = $this->getKeys($mdlName, $name, $pKey);
      foreach ($children as $child) {
        $results = $this->pushStack($name, $keys["fkey"], $child->$keys["id"]);
        if ($results) $models[] = $results;
      }
    }
    if (!$models) return;

    foreach ($models as $children) {
      $this->makeChainModels($children, $cascade);
    }
  }

  protected function pushStack($child, $fkey, $idValue)
  {
    $model  = MODEL($child);
    $models = $model->select($fkey, $idValue);

    if ($models) {
      $this->cascadeStack["{$child}:{$idValue}"] = $fkey;
    }

    return $models;
  }

  protected function getKeys($parent, $child, $pKey)
  {
    if (isset($this->keys[$parent][$child])) {
      return $this->keys[$parent][$child];
    } else {
      $tblName = convert_to_tablename($parent);
      return array("id" => "id", "fkey" => "{$tblName}_id");
    }
  }

  private function clearCascadeStack()
  {
    $stack = array_reverse($this->cascadeStack);

    foreach ($stack as $param => $fkey) {
      list($mdlName, $idValue) = explode(":", $param);
      MODEL($mdlName)->delete($fkey, $idValue);
    }
  }
}
