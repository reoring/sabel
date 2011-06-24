<?php

/**
 * Sabel_Rss_Reader_Atom10
 *
 * @category   RSS
 * @package    org.sabel.rss
 * @author     Ebine Yutaka <ebine.yutaka@sabel.jp>
 * @copyright  2004-2008 Mori Reo <mori.reo@sabel.jp>
 * @license    http://www.opensource.org/licenses/bsd-license.php  BSD License
 */
class Sabel_Rss_Reader_Atom10 extends Sabel_Rss_Reader_Abstract
{
  public function __construct(Sabel_Xml_Element $element)
  {
    $this->documentElement = $element;
    $this->itemsElement = $element->getChildren("entry");
  }
  
  /**
   * @return string
   */
  public function getHome()
  {
    if (($link = $this->documentElement->getChild("link")) === null) {
      return "";
    } else {
      return $link->getAttribute("href");
    }
  }
  
  /**
   * @return string
   */
  public function getTitle()
  {
    if (($title = $this->documentElement->getChild("title")) === null) {
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
    if (($subtitle = $this->documentElement->getChild("subtitle")) === null) {
      return "";
    } else {
      return $subtitle->getNodeValue();
    }
  }
  
  /**
   * @return string
   */
  public function getLastUpdated()
  {
    if (($date = $this->documentElement->getChild("updated")) === null) {
      if ($firstItem = $this->itemsElement->item(0)) {
        $date = $firstItem->getChild("updated");
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
        $object->uri = $link->getAttribute("href");
      }
      
      if ($summary = $item->getChild("summary")) {
        $object->content = $object->description = $summary->getNodeValue();
      }
      
      if ($content = $item->getChild("content")) {
        if ($content->at("mode") === "escaped") {
          $object->content = $content->getNodeValue();
        } else {
          $object->content = $content->getInnerContent();
        }
      }
      
      if ($date = $item->getChild("updated")) {
        $object->date = date("Y-m-d H:i:s", strtotime($date->getNodeValue()));
      }
      
      $items[] = $object;
    }
    
    return $items;
  }
}
