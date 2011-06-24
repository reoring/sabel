<?php

/**
 * Sabel_Rss_Writer_Rss
 *
 * @category   RSS
 * @package    org.sabel.rss
 * @author     Ebine Yutaka <ebine.yutaka@sabel.jp>
 * @copyright  2004-2008 Mori Reo <mori.reo@sabel.jp>
 * @license    http://www.opensource.org/licenses/bsd-license.php  BSD License
 */
class Sabel_Rss_Writer_Rss extends Sabel_Rss_Writer_Abstract
{
  public function build(array $items)
  {
    $rss = $this->document->createElement("rss");
    $rss->at("version", "2.0");
    $rss->at("xmlns:dc", "http://purl.org/dc/elements/1.1/");
    $rss->at("xmlns:content", "http://purl.org/rss/1.0/modules/content/");
    $rss->at("xml:lang", $this->info["language"]);
    
    $this->document->setDocumentElement($rss);
    
    $this->createChannel($rss);
    $this->createItems($rss, $items);
    
    return $this->document->toXML();
  }
  
  protected function createChannel($rss)
  {
    $info    = $this->info;
    $channel = $rss->addChild("channel");
    
    $channel->addChild("title")->setNodeValue(xmlescape($info["title"]));
    $channel->addChild("link")->setNodeValue(xmlescape($info["home"]));
    
    if (array_isset("description", $info)) {
      $channel->addChild("description")->setNodeValue(xmlescape($info["description"]));
    }
    
    if (array_isset("updated", $info)) {
      $channel->addChild("lastBuildDate")->setNodeValue(date("c", strtotime($info["updated"])));
    }
    
    if (array_isset("image", $info)) {
      $imgInfo = $info["image"];
      $image = $channel->addChild("image");
      
      if (array_isset("title", $imgInfo)) {
        $image->addChild("title")->setNodeValue(xmlescape($imgInfo["title"]));
      } elseif (array_isset("title", $info)) {
        $image->addChild("title")->setNodeValue(xmlescape($info["title"]));
      }
      
      if (array_isset("src", $imgInfo)) {
        $image->addChild("url")->setNodeValue(xmlescape($imgInfo["src"]));
      }
      
      if (array_isset("link", $imgInfo)) {
        $image->addChild("link")->setNodeValue(xmlescape($imgInfo["link"]));
      } elseif (array_isset("home", $info)) {
        $image->addChild("link")->setNodeValue(xmlescape($info["home"]));
      }
    }
  }
  
  protected function createItems($rss, $items)
  {
    foreach ($items as $item) {
      $itemElem = $rss->addChild("item");
      
      if (array_isset("title", $item)) {
        $itemElem->addChild("title")->setNodeValue(xmlescape($item["title"]));
      }
      
      $itemElem->addChild("link")->setNodeValue(xmlescape($item["link"]));
      
      $content = "";
      if (array_isset("content", $item)) {
        $content = $item["content"];
      } elseif (array_isset("description", $item)) {
        $content = $item["description"];
      }
      
      $itemElem->addChild("description")->setNodeValue($content, true);
      
      if (array_isset("date", $item)) {
        $itemElem->addChild("pubDate")->setNodeValue(date("r", strtotime($item["date"])));
      }
    }
  }
}
