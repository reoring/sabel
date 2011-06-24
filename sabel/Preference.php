<?php

/**
 * Preference
 *
 * @abstract
 * @category   Preference
 * @package    org.sabel.preference
 * @author     Mori Reo <mori.reo@sabel.jp>
 * @copyright  2004-2008 Mori Reo <mori.reo@sabel.jp>
 * @license    http://www.opensource.org/licenses/bsd-license.php  BSD License
 */
class Sabel_Preference
{
  const TYPE_INT     = "int";
  const TYPE_STRING  = "string";
  const TYPE_FLOAT   = "float";
  const TYPE_BOOLEAN = "boolean";
  const TYPE_ARRAY   = "array";
  const TYPE_OBJECT  = "object";

  private $backend = null;

  public static function create(Sabel_Config $config = null)
  {
    if (!$config instanceof Sabel_Config) {
      return new self();
    }

    $arrayConfig = $config->configure();

    if (!is_array($arrayConfig)) {
      $arrayConfig = array();
    }

    if (isset($arrayConfig["backend"])) {
      $backendClass = $arrayConfig["backend"];

      if (!class_exists($backendClass)) {
        $msg = "specified backend class " . $backendClass . " is not found in any classpath";
        throw new Sabel_Exception_ClassNotFound($msg);
      }

      $backend = new $backendClass($arrayConfig);

      return new self($backend);
    }
  }

  public function __construct($backend = null)
  {
    if ($backend == null) {
      $backend = new Sabel_Preference_Xml();
    }

    $this->backend = $backend;
  }

  /**
   * check backend contains a preference.
   *
   * @param $key string
   */
  public function contains($key)
  {
    return $this->backend->has($key);
  }

  public function getAll()
  {
    $map = array();

    foreach ($this->backend->getAll() as $key => $set) {
      $map[$key] = $this->convertType($set["value"], $set["type"]);
    }

    return $map;
  }

  private function convertType($value, $type)
  {
    switch ($type) {
      case self::TYPE_INT:     return (int)     $value;
      case self::TYPE_FLOAT:   return (float)   $value;
      case self::TYPE_STRING:  return (string)  $value;
      case self::TYPE_BOOLEAN: return (boolean) $value;
    }
  }

  public function setInt($key, $value)
  {
    if (!is_int($value)) {
      $value = (int) $value;
    }

    $this->backend->set($key, $value, self::TYPE_INT);
  }

  public function getInt($key, $default = null)
  {
    if ($default !== null && !is_int($default)) {
      $default = (int) $default;
    }

    $result = $this->get($key, $default, self::TYPE_INT);

    if (!is_int($result)) {
      return (int) $result;
    }

    return $result;
  }

  public function setFloat($key, $value)
  {
    if (!is_float($value)) {
      $value = (float) $value;
    }

    $this->backend->set($key, $value, self::TYPE_FLOAT);
  }

  public function getFloat($key, $default = null)
  {
    if ($default !== null && !is_float($default)) {
      $default = (float) $default;
    }

    $result = $this->get($key, $default, self::TYPE_FLOAT);

    if (!is_float($result)) {
      return (float) $result;
    }

    return $result;
  }

  public function setDouble($key, $value)
  {
    $this->setFloat($key, $vlaue);
  }

  public function getDouble($key, $default = null)
  {
    $this->getFloat($key, $default);
  }

  public function setString($key, $value)
  {
    if (!is_string($value)) {
      $value = (string) $value;
    }

    $this->backend->set($key, $value, self::TYPE_STRING);
  }

  public function getString($key, $default = null)
  {
    if ($default !== null && !is_string($default)) {
      $default = (string) $default;
    }

    $result = $this->get($key, $default, self::TYPE_STRING);

    if (!is_string($result)) {
      return (string) $result;
    }

    return $result;
  }

  public function setBoolean($key, $value)
  {
    if (!is_bool($value)) {
      $value = (bool) $value;
    }

    $this->backend->set($key, $value, self::TYPE_BOOLEAN);
  }

  public function getBoolean($key, $default = null)
  {
    if ($default !== null && !is_bool($default)) {
      $default = (boolean) $default;
    }

    $result = $this->get($key, $default, self::TYPE_BOOLEAN);

    if (!is_bool($result)) {
      return (boolean) $result;
    }

    return $result;
  }

  public function setArray($key, $value)
  {
    if (!is_array($value)) {
      throw new Sabel_Exception_Runtime("setArray value must be an array type");
    }

    $this->backend->set($key, serialize($value), self::TYPE_ARRAY);
  }

  public function getArray($key, $default = null)
  {
    if ($default !== null && !is_array($default)) {
      throw new Sabel_Exception_Runtime("setArray value must be an array type");
    }

    if ($default !== null) {
      $this->setArray($key, $default);

      return $default;
    }

    if (!$this->backend->has($key) && $default === null) {
      throw new Sabel_Exception_Runtime("preference ${key} not found");
    }

    return unserialize($this->backend->get($key));
  }

  public function setObject($key, $value)
  {
    if (!is_object($value)) {
      throw new Sabel_Exception_Runtime("setObject value must be an object type");
    }

    $this->backend->set($key, base64_encode(serialize($value)), self::TYPE_OBJECT);
  }

  public function getObject($key, $default = null)
  {
    if ($default !== null && !is_object($default)) {
      throw new Sabel_Exception_Runtime("setObject value must be an object type");
    }

    if ($default !== null) {
      $this->setObject($key, $default);

      return $default;
    }

    if (!$this->backend->has($key) && $default === null) {
      throw new Sabel_Exception_Runtime("preference ${key} not found");
    }

    return unserialize(base64_decode($this->backend->get($key)));
  }

  /**
   * delete preference
   *
   * @param string $key
   * @return mixed
   */
  public function delete($key)
  {
    if ($this->backend->has($key)) {
      $removedValue = $this->backend->get($key);

      $this->backend->delete($key);

      return $removedValue;
    }
  }

  private function get($key, $default, $type)
  {
    if ($default !== null) {
      $this->backend->set($key, $default, $type);
      return $default;
    }

    if (!$this->backend->has($key) && $default === null) {
      throw new Sabel_Exception_Runtime("preference ${key} not found");
    }

    return $this->backend->get($key);
  }
}
