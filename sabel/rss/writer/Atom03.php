<?php

/**
 * Sabel_Rss_Writer_Atom03
 *
 * @category   RSS
 * @package    org.sabel.rss
 * @author     Ebine Yutaka <ebine.yutaka@sabel.jp>
 * @copyright  2004-2008 Mori Reo <mori.reo@sabel.jp>
 * @license    http://www.opensource.org/licenses/bsd-license.php  BSD License
 */
class Sabel_Rss_Writer_Atom03 extends Sabel_Rss_Writer_Abstract
{
  public function build(array $items)
  {
    $feed = $this->document->createElement("feed");
    $feed->at("version",  "0.3");
    $feed->at("xmlns",    "http://purl.org/atom/ns#");
    $feed->at("xmlns:dc", "http://purl.org/dc/elements/1.1/");
    $feed->at("xml:lang", $this->info["language"]);
    
    $this->document->setDocumentElement($feed);
    
    $info = $this->info;
    if (array_isset("title", $info)) {
      $feed->addChild("title")->setNodeValue(xmlescape($info["title"]));
    }
    
    $link = $feed->addChild("link");
    $link->at("rel", "alternate")->at("type", "text/html")->at("href", $info["home"]);
    
    if (array_isset("description", $info)) {
      $feed->addChild("tagline")->setNodeValue(xmlescape($info["description"]));
    }
    
    if (array_isset("updated", $info)) {
      $feed->addChild("modified")->setNodeValue(date("c", strtotime($info["updated"])));
    }
    
    $this->createItems($feed, $items);
    
    return $this->document->toXML();
  }
  
  protected function createItems($feed, $items)
  {
    foreach ($items as $item) {
      $itemElem = $feed->addChild("entry");
      
      if (array_isset("title", $item)) {
        $itemElem->addChild("title")->setNodeValue(xmlescape($item["title"]));
      }
      
      $link = $itemElem->addChild("link");
      $link->at("rel", "alternate")->at("type", "text/html")->at("href", $item["link"]);
      
      if (array_isset("description", $item)) {
        $summary = $itemElem->addChild("summary");
        $summary->at("type", "text/plain");
        $summary->setNodeValue(xmlescape($item["description"]));
      }
      
      if (array_isset("content", $item)) {
        $content = $itemElem->addChild("content");
        $content->at("type", "text")->at("mode", "escaped");
        $content->setNodeValue($item["content"], true);
      }
      
      if (array_isset("date", $item)) {
        $itemElem->addChild("modified")->setNodeValue(date("c", strtotime($item["date"])));
      }
    }
  }
}
