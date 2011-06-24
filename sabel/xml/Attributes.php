<?php

/**
 * Sabel_Xml_Attributes
 *
 * @category   XML
 * @package    org.sabel.xml
 * @author     Ebine Yutaka <ebine.yutaka@sabel.jp>
 * @copyright  2004-2008 Mori Reo <mori.reo@sabel.jp>
 * @license    http://www.opensource.org/licenses/bsd-license.php  BSD License
 */
class Sabel_Xml_Attributes extends Sabel_Object
{
  /**
   * @var DOMNamedNodeMap
   */
  protected $attributes = null;
  
  public function __construct(DOMNamedNodeMap $attributes)
  {
    $this->attributes = $attributes;
  }
  
  public function toArray()
  {
    $ret = array();
    $attributes = $this->attributes;
    
    foreach ($attributes as $attribute) {
      $ret[$attribute->name] = $attributes->getNamedItem($attribute->name)->value;
    }
    
    return $ret;
  }
  
  public function get($key)
  {
    if (($attr = $this->attributes->getNamedItem($key)) === null) {
      return null;
    } else {
      return $attr->value;
    }
  }
  
  /**
   * Not yet implemented
   *
  public function set($key, $value)
  {
    $this->attributes->setNamedItem($key, $value);
    
    return $this;
  }
   */
  
  public function has($key)
  {
    return ($this->attributes->getNamedItem($key) !== null);
  }
  
  public function __get($key)
  {
    return $this->get($key);
  }
}
