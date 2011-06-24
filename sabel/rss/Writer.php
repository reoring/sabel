<?php

/**
 * Sabel_Rss_Writer
 *
 * @category   RSS
 * @package    org.sabel.rss
 * @author     Ebine Yutaka <ebine.yutaka@sabel.jp>
 * @copyright  2004-2008 Mori Reo <mori.reo@sabel.jp>
 * @license    http://www.opensource.org/licenses/bsd-license.php  BSD License
 */
class Sabel_Rss_Writer extends Sabel_Object
{
  /**
   * @var string
   */
  protected $type = "Rss";
  
  /**
   * @var array
   */
  protected $info = array(
    "xmlVersion"  => "1.0",
    "encoding"    => "UTF-8",
    "language"    => "en",
    "home"        => "",
    "image"       => array(),
    "rss"         => "",
    "title"       => "",
    "description" => "",
    "updated"     => "",
  );
  
  /**
   * @var array
   */
  protected $items = array();
  
  /**
   * @var int
   */
  protected $summaryLength = 0;
  
  public function __construct($type = "Rss")
  {
    $type = ucfirst(strtolower($type));
    $type = str_replace(".", "", $type);
    
    if ($type === "Rss20") {
      $this->type = "Rss";
    } elseif ($type === "Rss10") {
      $this->type = "Rdf";
    } elseif (in_array($type, array("Rss", "Rdf", "Atom10", "Atom03"), true)) {
      $this->type = $type;
    } else {
      $message = __METHOD__ . "() '{$type}' is not supported now.";
      throw new Sabel_Exception_Runtime($message);
    }
  }
  
  /**
   * @param array $info
   *
   * @return self
   */
  public function setFeedInfo(array $info)
  {
    $this->info = array_merge($this->info, $info);
    
    return $this;
  }
  
  public function setHome($url)
  {
    $this->info["home"] = $url;
    
    return $this;
  }
  
  public function setTitle($title)
  {
    $this->info["title"] = $title;
    
    return $this;
  }
  
  public function setDescription($description)
  {
    $this->info["description"] = $description;
    
    return $this;
  }
  
  public function setImage($imgInfo)
  {
    if (is_array($imgInfo)) {
      $this->info["image"] = $imgInfo;
    } elseif (is_string($imgInfo)) {
      $this->info["image"]["src"] = $imgInfo;
    } else {
      $message = __METHOD__ . "() argument must be an array or string.";
      throw new Sabel_Exception_InvalidArgument($message);
    }
    
    return $this;
  }
  
  /**
   * @param array $data
   *
   * @return self
   */
  public function addItem(array $item)
  {
    if (array_isset("url", $item)) {
      $item["link"] = $item["url"];
    }
    
    if (array_isset("summary", $item)) {
      $item["description"] = $item["summary"];
    }
    
    if (!array_isset("link", $item)) {
      $message = __METHOD__ . "() empty item url.";
      throw new Sabel_Exception_Runtime($message);
    }
    
    $this->items[] = $item;
    
    return $this;
  }
  
  /**
   * @param int $length
   *
   * @return self
   */
  public function setSummaryLength($length)
  {
    if (is_natural_number($length)) {
      $this->summaryLength = $length;
    } else {
      $message = __METHOD__ . "() argument must be an integer.";
      throw new Sabel_Exception_Runtime($message);
    }
    
    return $this;
  }
  
  /**
   * @param string $path
   *
   * @return string
   */
  public function output($path = null)
  {
    $items = $this->items;
    
    if ($this->summaryLength > 0) {
      $length = $this->summaryLength;
      if (extension_loaded("mbstring")) {
        foreach ($items as &$item) {
          $item["summary"] = mb_strimwidth($item["content"], 0, $length + 3, "...");
        }
      } else {
        foreach ($items as &$item) {
          if (strlen($item["content"]) > $length) {
            $item["summary"] = substr($item["content"], 0, $length - 3) . "...";
          }
        }
      }
    }
    
    $info = $this->info;
    if (!array_isset("home", $info)) {
      $info["home"] = "http://" . get_server_name() . "/";
    }
    
    if (!array_isset("updated", $info) && isset($items[0])) {
      $info["updated"] = (isset($items[0]["date"])) ? $items[0]["date"] : now();
    }
    
    $className = "Sabel_Rss_Writer_" . $this->type;
    $instance = new $className($info);
    
    $xml = $instance->build($items);
    
    if ($path !== null) {
      file_put_contents($path, $xml);
    }
    
    return $xml;
  }
}
