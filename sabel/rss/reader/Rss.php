<?php

/**
 * Sabel_Rss_Reader_Rss
 *
 * @category   RSS
 * @package    org.sabel.rss
 * @author     Ebine Yutaka <ebine.yutaka@sabel.jp>
 * @copyright  2004-2008 Mori Reo <mori.reo@sabel.jp>
 * @license    http://www.opensource.org/licenses/bsd-license.php  BSD License
 */
class Sabel_Rss_Reader_Rss extends Sabel_Rss_Reader_Abstract
{
  /**
   * @var Sabel_Xml_Element
   */
  protected $channel = null;
  
  public function __construct(Sabel_Xml_Element $element)
  {
    $this->documentElement = $element;
    
    $this->channel = $element->getChild("channel");
    $this->itemsElement = $this->channel->getChildren("item");
  }
  
  /**
   * @return string
   */
  public function getHome()
  {
    if (($link = $this->channel->getChild("link")) === null) {
      return "";
    } else {
      return $link->getNodeValue();
    }
  }
  
  /**
   * @return string
   */
  public function getTitle()
  {
    if (($title = $this->channel->getChild("title")) === null) {
      return "";
    } else {
      return $title->getNodeValue();
    }
  }
  
  /**
   * @return string
   */
  public function getDescription()
  {
    if (($desc = $this->channel->getChild("description")) === null) {
      return "";
    } else {
      return $desc->getNodeValue();
    }
  }
  
  /**
   * @return string
   */
  public function getLastUpdated()
  {
    if (($date = $this->channel->getChild("lastBuildDate")) === null) {
      if ($firstItem = $this->itemsElement->item(0)) {
        $date = $firstItem->getChild("pubDate");
      } else {
        return null;
      }
    }
    
    return date("Y-m-d H:i:s", strtotime($date->getNodeValue()));
  }
  
  /**
   * @return Sabel_ValueObject[]
   */
  public function getItems()
  {
    $items = array();
    foreach ($this->itemsElement as $i => $item) {
      $object = new Sabel_ValueObject();
      
      if ($title = $item->getChild("title")) {
        $object->title = $title->getNodeValue();
      }
      
      if ($link = $item->getChild("link")) {
        $object->uri = $link->getNodeValue();
      }
      
      if ($desc = $item->getChild("description")) {
        $object->content = $object->description = $desc->getNodeValue();
      }
      
      if ($category = $item->getChild("category")) {
        $object->category = $category->getNodeValue();
      }
      
      if ($date = $item->getChild("pubDate")) {
        $object->date = date("Y-m-d H:i:s", strtotime($date->getNodeValue()));
      }
      
      $items[] = $object;
    }
    
    return $items;
  }
}
