<?php

/**
 * Memcache backend driver of preference.
 *
 * @abstract
 * @category   Preference
 * @package    org.sabel.preference
 * @author     Mori Reo <mori.reo@sabel.jp>
 * @copyright  2004-2008 Mori Reo <mori.reo@sabel.jp>
 * @license    http://www.opensource.org/licenses/bsd-license.php  BSD License
 */
class Sabel_Preference_Memcache implements Sabel_Preference_Backend
{
  const DEFUALT_NAMESPACE = "default";

  const ALL_KEYS = "_PREFERENCE_KYES_";

  private $config = null;
  private $memcache = null;
  private $namespace = null;

  public function __construct($config = array())
  {
    $this->config = $config;

    if (!extension_loaded("memcache")) {
      throw new Sabel_Exception_Runtime("memcache extension not loaded.");
    }

    if (isset($config["namespace"])) {
      $this->namespace = $config["namespace"];
    } else {
      $this->namespace = self::DEFUALT_NAMESPACE;
    }

    if (isset($config["server"])) {
      $server = $config["server"];
    } else {
      $server = "localhost";
    }

    if (isset($config["port"])) {
      $port = $config["port"];
    } else {
      $port = 11211;
    }

    $this->memcache = new Memcache();
    $this->memcache->addServer($server, $port);
  }

  public function set($key, $value, $type)
  {
    $keys = $this->memcache->get(self::ALL_KEYS);

    if ($keys === false) {
      $keys = array();
    }

    $keys[$this->genKey($key)] = $this->genKey($key);

    $this->memcache->set(self::ALL_KEYS, $keys);

    if (Sabel_Preference::TYPE_BOOLEAN == $type) {
      if ($value === false) {
        $value = 0;
      }
    }

    $this->memcache->set($this->genKey($key), $value);
    $this->memcache->set($this->genTypeKey($key), $type);
  }

  public function has($key)
  {
    return ($this->memcache->get($this->genKey($key)) !== false);
  }

  public function get($key)
  {
    $value = $this->memcache->get($this->genKey($key));
    $type  = $this->memcache->get($this->genTypeKey($key));

    if ($type === Sabel_Preference::TYPE_BOOLEAN && $value === 0) {
      return false;
    }

    return $value;
  }

  public function delete($key)
  {
    $keys = $this->memcache->get(self::ALL_KEYS);
    unset($keys[$this->genKey($key)]);
    $this->memcache->set(self::ALL_KEYS, $keys);

    return $this->memcache->delete($this->genKey($key));
  }

  public function getAll()
  {
    $map = array();

    $keys = $this->memcache->get(self::ALL_KEYS);

    foreach ($keys as $key) {
      if (strpos($key, $this->namespace . "::") !== false) {
        list($ns, $key) = explode("::", $key);

        $value = $this->memcache->get($this->genKey($key));

        if ($value === "false_false") {
          $value = false;
        }

        $map[$key] = array("value" => $value,
                           "type"  => $this->memcache->get($this->genTypeKey($key)));
      }
    }

    return $map;
  }

  private function genKey($key)
  {
    return $this->namespace . "::" . $key;
  }

  private function genTypeKey($key)
  {
    return $this->genKey($key) . "::type";
  }
}
