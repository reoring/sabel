<?php

/**
 * database backend driver of preference.
 *
 * @abstract
 * @category   Preference
 * @package    org.sabel.preference
 * @author     Mori Reo <mori.reo@sabel.jp>
 * @copyright  2004-2008 Mori Reo <mori.reo@sabel.jp>
 * @license    http://www.opensource.org/licenses/bsd-license.php  BSD License
 */
class Sabel_Preference_Database implements Sabel_Preference_Backend
{
  const DEFUALT_NAMESPACE = "default";

  private $config = null;
  private $model = null;
  private $namespace = null;

  public function __construct($config = array())
  {
    $this->config = $config;

    if (isset($this->config["namespace"])) {
      $this->namespace = $this->config["namespace"];
    } else {
      $this->namespace = self::DEFUALT_NAMESPACE;
    }

    if (isset($config["model"])) {
      $this->model = MODEL($config["model"]);
    } else {
      $this->model = MODEL("SblPreference");
    }
  }

  public function set($key, $value, $type)
  {
    $this->model->setCondition("namespace", $this->namespace);
    $this->model->setCondition("key", $key);

    Sabel_Db_Transaction::activate();

    try {
      $this->model = $this->model->selectOne();

      $this->model->namespace = $this->namespace;
      $this->model->key   = $key;
      $this->model->value = $value;
      $this->model->type  = $type;

      $this->model->save();

      Sabel_Db_Transaction::commit();
    } catch (Exception $e) {
      Sabel_Db_Transaction::rollback();
    }
  }

  public function has($key)
  {
    $this->model->setCondition("namespace", $this->namespace);
    $this->model->setCondition("key", $key);

    return ($this->model->getCount() !== 0);
  }

  public function get($key)
  {
    $this->model->setCondition("namespace", $this->namespace);
    $this->model->setCondition("key", $key);

    $result = $this->model->selectOne();

    return $result->value;
  }

  public function delete($key)
  {
    $this->model->setCondition("namespace", $this->namespace);
    $this->model->setCondition("key", $key);

    Sabel_Db_Transaction::activate();

    try {
      $this->model->delete();
      Sabel_Db_Transaction::commit();
    } catch (Exception $e) {
      Sabel_Db_Transaction::rollback();
    }
  }

  public function getAll()
  {
    $map = array();

    $this->model->setCondition("namespace", $this->namespace);

    foreach ($this->model->select() as $model) {
      $map[$model->key] = array("value" => $model->value,
                                "type"  => $model->type);
    }

    return $map;
  }
}
