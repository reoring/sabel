<?php

/**
 * Sabel_Rss_Writer_Rdf
 *
 * @category   RSS
 * @package    org.sabel.rss
 * @author     Ebine Yutaka <ebine.yutaka@sabel.jp>
 * @copyright  2004-2008 Mori Reo <mori.reo@sabel.jp>
 * @license    http://www.opensource.org/licenses/bsd-license.php  BSD License
 */
class Sabel_Rss_Writer_Rdf extends Sabel_Rss_Writer_Abstract
{
  public function build(array $items)
  {
    $rdf = $this->document->createElement("rdf:RDF");
    $rdf->at("xmlns", "http://purl.org/rss/1.0/");
    $rdf->at("xmlns:rdf", "http://www.w3.org/1999/02/22-rdf-syntax-ns#");
    $rdf->at("xmlns:content", "http://purl.org/rss/1.0/modules/content/");
    $rdf->at("xmlns:dc", "http://purl.org/dc/elements/1.1/");
    $rdf->at("xml:lang", $this->info["language"]);
    
    $this->document->setDocumentElement($rdf);
    
    $this->createChannel($rdf, $items);
    $this->createImage($rdf);
    $this->createItems($rdf, $items);
    
    return $this->document->toXML();
  }
  
  protected function createChannel($rdf, $items)
  {
    $info    = $this->info;
    $dom     = $this->document;
    $channel = $rdf->addChild("channel");
    
    if (array_isset("rss", $info)) {
      $channel->setAttribute("rdf:about", $info["rss"]);
    }
    
    $channel->addChild("title")->setNodeValue(xmlescape($info["title"]));
    $channel->addChild("link")->setNodeValue(xmlescape($info["home"]));
    
    if (array_isset("description", $info)) {
      $channel->addChild("description")->setNodeValue(xmlescape($info["description"]));
    }
    
    if (array_isset("image[src]", $info)) {
      $channel->addChild("image")->at("rdf:resource", $info["image"]["src"]);
    }
    
    if (array_isset("updated", $info)) {
      $channel->addChild("dc:date")->setNodeValue(date("c", strtotime($info["updated"])));
    }
    
    $seq = $channel->addChild("items")->addChild("rdf:Seq");
    
    foreach ($items as $item) {
      $seq->addChild("rdf:li")->at("rdf:resource", $item["link"]);
    }
  }
  
  protected function createItems($rdf, $items)
  {
    foreach ($items as $item) {
      $itemElem = $rdf->addChild("item");
      $itemElem->at("rdf:about", xmlescape($item["link"]));
      
      if (array_isset("title", $item)) {
        $itemElem->addChild("title")->setNodeValue(xmlescape($item["title"]));
      }
      
      $itemElem->addChild("link")->setNodeValue(xmlescape($item["link"]));
      
      if (array_isset("description", $item)) {
        $itemElem->addChild("description")->setNodeValue(xmlescape($item["description"]));
      }
      
      if (array_isset("content", $item)) {
        $itemElem->addChild("content:encoded")->setNodeValue($item["content"], true);
      }
      
      if (array_isset("date", $item)) {
        $itemElem->addChild("date")->setNodeValue(date("c", strtotime($item["date"])));
      }
    }
  }
  
  protected function createImage($rdf)
  {
    $info = $this->info;
    if (array_isset("image", $info)) {
      $imgInfo = $info["image"];
      $image = $rdf->addChild("image");
      
      if (array_isset("title", $imgInfo)) {
        $image->addChild("title")->setNodeValue(xmlescape($imgInfo["title"]));
      } elseif (array_isset("title", $info)) {
        $image->addChild("title")->setNodeValue(xmlescape($info["title"]));
      }
      
      if (array_isset("src", $imgInfo)) {
        $image->at("rdf:about", xmlescape($imgInfo["src"]));
        $image->addChild("url")->setNodeValue(xmlescape($imgInfo["src"]));
      }
      
      if (array_isset("link", $imgInfo)) {
        $image->addChild("link")->setNodeValue(xmlescape($imgInfo["link"]));
      } elseif (array_isset("home", $info)) {
        $image->addChild("link")->setNodeValue(xmlescape($info["home"]));
      }
    }
  }
}
