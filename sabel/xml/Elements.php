<?php

/**
 * Sabel_Xml_Elements
 *
 * @category   XML
 * @package    org.sabel.xml
 * @author     Ebine Yutaka <ebine.yutaka@sabel.jp>
 * @copyright  2004-2008 Mori Reo <mori.reo@sabel.jp>
 * @license    http://www.opensource.org/licenses/bsd-license.php  BSD License
 */
class Sabel_Xml_Elements extends Sabel_Object implements Iterator, ArrayAccess
{
  /**
   * @var int
   */
  public $length = 0;
  
  /**
   * @var Sabel_Xml_Element[]
   */
  protected $elements = array();
  
  /**
   * @var int
   */
  protected $pointer = 0;
  
  public function __construct(array $elements)
  {
    $this->elements = $elements;
    $this->length = count($elements);
  }
  
  public function getRawElements()
  {
    return $this->elements;
  }
  
  public function getElementAt($index)
  {
    if (isset($this->elements[$index])) {
      return $this->elements[$index];
    } else {
      return null;
    }
  }
  
  public function item($index)
  {
    return $this->getElementAt($index);
  }
  
  public function getDocument()
  {
    if ($this->length > 0) {
      return $this->elements[0]->getDocument();
    } else {
      return null;
    }
  }
  
  public function reverse()
  {
    $this->elements = array_reverse($this->elements);
    
    return $this;
  }
  
  public function getParent()
  {
    if ($this->length > 0) {
      return $this->elements[0]->getParent();
    } else {
      return null;
    }
  }
  
  public function append($element)
  {
    if (!$element instanceof Sabel_Xml_Element) {
      $element = new Sabel_Xml_Element($element);
    }
    
    $this->getParent()->appendChild($element);
    
    $this->elements[] = $element;
    $this->length++;
  }
  
  public function appendChild($element)
  {
    $this->append($element);
  }
  
  public function appendLast($element)
  {
    $this->append($element);
  }
  
  public function appendFirst($element)
  {
    if (!$element instanceof Sabel_Xml_Element) {
      $element = new Sabel_Xml_Element($element);
    }
    
    $first = $this->getParent()->getFirstChild();
    $first->insertBefore($element);
    
    array_unshift($this->elements, $element);
    $this->length++;
  }
  
  public function __get($key)
  {
    if ($element = $this->getElementAt(0)) {
      return $element->$key;
    } else {
      return null;
    }
  }
  
  public function __call($method, $args)
  {
    if ($element = $this->getElementAt(0)) {
      return call_user_func_array(array($element, $method), $args);
    } else {
      $message = __METHOD__ . "() has no elements.";
      throw new Sabel_Exception_Runtime($message);
    }
  }
  
  public function offsetGet($index)
  {
    return $this->getElementAt($index);
  }
  
  public function offsetSet($offset, $value)
  {
    // @todo warning
  }
  
  public function offsetExists($index)
  {
    return isset($this->elements[$index]);
  }
  
  public function offsetUnset($index)
  {
    if (isset($this->elements[$index])) {
      unset($this->elements[$index]);
      $this->length--;
    }
  }
  
  public function current()
  {
    if (isset($this->elements[$this->pointer])) {
      return $this->elements[$this->pointer];
    } else {
      return null;
    }
  }
  
  public function key()
  {
    return $this->pointer;
  }
  
  public function next()
  {
    $this->pointer++;
  }
  
  public function rewind()
  {
    $this->pointer = 0;
  }
  
  public function valid()
  {
    return ($this->pointer < $this->length);
  }
}
