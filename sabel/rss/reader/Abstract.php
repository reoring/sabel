<?php

/**
 * Sabel_Rss_Reader_Abstract
 *
 * @abstract
 * @category   RSS
 * @package    org.sabel.rss
 * @author     Ebine Yutaka <ebine.yutaka@sabel.jp>
 * @copyright  2004-2008 Mori Reo <mori.reo@sabel.jp>
 * @license    http://www.opensource.org/licenses/bsd-license.php  BSD License
 */
abstract class Sabel_Rss_Reader_Abstract extends Sabel_Object
{
  /**
   * @var Sabel_Xml_Element
   */
  protected $documentElement = null;
  
  /**
   * @var Sabel_Xml_Elements
   */
  protected $itemsElement = null;
  
  /**
   * @var int
   */
  protected $pointer = 0;
  
  /**
   * @param Sabel_Xml_Element $element
   */
  abstract public function __construct(Sabel_Xml_Element $element);
  
  /**
   * @return string
   */
  abstract public function getHome();
  
  /**
   * @return string
   */
  abstract public function getTitle();
  
  /**
   * @return string
   */
  abstract public function getDescription();
  
  /**
   * @return string
   */
  abstract public function getLastUpdated();
  
  /**
   * @return Sabel_ValueObject[]
   */
  abstract public function getItems();
}
