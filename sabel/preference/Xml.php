<?php

/**
 * xml backend driver of preference.
 *
 * @abstract
 * @category   Preference
 * @package    org.sabel.preference
 * @author     Mori Reo <mori.reo@sabel.jp>
 * @copyright  2004-2008 Mori Reo <mori.reo@sabel.jp>
 * @license    http://www.opensource.org/licenses/bsd-license.php  BSD License
 */
class Sabel_Preference_Xml implements Sabel_Preference_Backend
{
  const DEFUALT_FILENAME = "default.xml";

  private $filepath = null;

  private $document;
  private $rootNode;

  private $config = null;

  public function __construct($config = array())
  {
    $this->config = $config;

    if (!defined("RUN_BASE")) {
      throw new Sabel_Exception_Runtime("RUN_BASE can't be undefined");
    }

    $preferencesDir = RUN_BASE . DS . "data" . DS . "preferences";

    if (isset($this->config["file"])) {
      $file = $this->config["file"];

      if (strpos($file, ".") === false) {
        $file .= ".xml";
      }

      if (isset($config["absolute"])) {
        $this->filepath = $file;
      } else {
        $this->filepath = $preferencesDir . DS . $file;
      }
    }

    if ($this->filepath === null) {
      $this->filepath = $preferencesDir . DS . "default.xml";
    }

    if (dirname($this->filepath) !== "." && !is_dir(dirname($this->filepath))) {
      if (!mkdir(dirname($this->filepath), 0755)) {
        throw new Sabel_Exception_Runtime("can't make directory " . dirname($this->filepath) . " check configuration");
      }
    }

    if (!is_readable($this->filepath)) {
      if (!touch($this->filepath, 0644)) {
        throw new Sabel_Exception_Runtime("can't create " . $this->filepath . " check configuration");
      }

      file_put_contents($this->filepath, '<?xml version="1.0" encoding="utf-8"?><preferences/>');
    }

    $this->document = Sabel_Xml_Document::create();
    $this->rootNode = $this->document->load("XML", $this->filepath);
  }

  public function set($key, $value, $type)
  {
    if ($this->rootNode->$key->length === 0) {
      $this->rootNode->addChild($key)->at("value", $value)->at("type", $type);
    } else {
      $this->rootNode->$key->at("value", $value)->at("type", $type);
    }

    $this->document->save();
  }

  public function has($key)
  {
    if (!isset($this->rootNode->$key)) {
      return false;
    }

    return ($this->rootNode->$key->length !== 0);
  }

  public function get($key)
  {
    if ($this->has($key)) {
      return $this->rootNode->$key->at("value");
    }
  }

  public function getAll()
  {
    $map = array();

    foreach ($this->rootNode->getChildren() as $child) {
      $map[$child->tagName] = array("value" => $child->at("value"),
                                    "type"  => $child->at("type"));
    }

    return $map;
  }

  public function delete($key)
  {
    if ($this->has($key)) {
      $this->document->save();
      return $this->rootNode->$key = null;
    }
  }
}
