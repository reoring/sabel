<?php

/**
 * Sabel_Rss_Reader_Atom03
 *
 * @category   RSS
 * @package    org.sabel.rss
 * @author     Ebine Yutaka <ebine.yutaka@sabel.jp>
 * @copyright  2004-2008 Mori Reo <mori.reo@sabel.jp>
 * @license    http://www.opensource.org/licenses/bsd-license.php  BSD License
 */
class Sabel_Rss_Reader_Atom03 extends Sabel_Rss_Reader_Abstract
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
    $links = $this->documentElement->getChildren("link");
    
    foreach ($links as $link) {
      if ($link->getAttribute("rel") === "alternate") {
        return $link->getAttribute("href");
      }
    }
    
    return "";
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
    if (($tagline = $this->documentElement->getChild("tagline")) === null) {
      return "";
    } else {
      return $tagline->getNodeValue();
    }
  }
  
  /**
   * @return string
   */
  public function getLastUpdated()
  {
    if (($date = $this->documentElement->getChild("modified")) === null) {
      if ($firstItem = $this->itemsElement->item(0)) {
        $date = $firstItem->getChild("modified");
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
      
      foreach ($item->getChildren("link") as $link) {
        if ($link->getAttribute("rel") === "alternate") {
          $object->uri = $link->getAttribute("href");
          break;
        }
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
      
      if ($date = $item->getChild("modified")) {
        $object->date = date("Y-m-d H:i:s", strtotime($date->getNodeValue()));
      }
      
      $items[] = $object;
    }
    
    return $items;
  }
}
