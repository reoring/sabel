<?php

/**
 * Sabel_Rss_Writer_Abstract
 *
 * @interface
 * @category   RSS
 * @package    org.sabel.rss
 * @author     Ebine Yutaka <ebine.yutaka@sabel.jp>
 * @copyright  2004-2008 Mori Reo <mori.reo@sabel.jp>
 * @license    http://www.opensource.org/licenses/bsd-license.php  BSD License
 */
abstract class Sabel_Rss_Writer_Abstract extends Sabel_Object
{
  /**
   * @var array
   */
  protected $info = array();
  
  /**
   * @var DOMDocument
   */
  protected $document = null;
  
  abstract public function build(array $items);
  
  public function __construct(array $info)
  {
    $this->info = $info;
    
    $arg = array();
    if (array_isset("xmlVersion", $info)) {
      $arg["version"] = $info["xmlVersion"];
    }
    
    if (array_isset("encoding", $info)) {
      $arg["encoding"] = $info["encoding"];
    }
    
    $this->document = Sabel_Xml_Document::create($arg);
  }
}
