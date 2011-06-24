<?php

/**
 * Sabel_Rss_Reader
 *
 * @category   RSS
 * @package    org.sabel.rss
 * @author     Ebine Yutaka <ebine.yutaka@sabel.jp>
 * @copyright  2004-2008 Mori Reo <mori.reo@sabel.jp>
 * @license    http://www.opensource.org/licenses/bsd-license.php  BSD License
 */
class Sabel_Rss_Reader
{
  public static function loadUrl($url, $xmlConfig = array())
  {
    return self::load(file_get_contents($url), $xmlConfig);
  }
  
  public static function loadFile($path, $xmlConfig = array())
  {
    if (is_file($path) && is_readable($path)) {
      return self::load(file_get_contents($path), $xmlConfig);
    } else {
      $message = __METHOD__ . "() file not found or permission denied.";
      throw new Sabel_Exception_Runtime($message);
    }
  }
  
  public static function loadString($contents, $xmlConfig = array())
  {
    return self::load($contents, $xmlConfig);
  }
  
  protected static function load($contents, $xmlConfig)
  {
    $document = Sabel_Xml_Document::create($xmlConfig);
    $element = $document->loadXML($contents);
    
    if ($element === null) {
      $message = __METHOD__ . "() invalid xml.";
      throw new Sabel_Exception_Runtime($message);
    }
    
    switch (strtolower($element->tagName)) {
    case "rdf:rdf":
      return new Sabel_Rss_Reader_Rdf($element);
    case "rss":
      return new Sabel_Rss_Reader_Rss($element);
      break;
    case "feed":
      if ($element->getAttribute("version") === "0.3") {
        return new Sabel_Rss_Reader_Atom03($element);
      } else {
        return new Sabel_Rss_Reader_Atom10($element);
      }
      break;
    default:
      $message = __METHOD__ . "() unknown feed format.";
      throw new Sabel_Exception_Runtime($message);
    }
  }
}
