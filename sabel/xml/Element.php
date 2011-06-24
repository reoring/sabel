<?php

/**
 * Sabel_Xml_Element
 *
 * @category   XML
 * @package    org.sabel.xml
 * @author     Ebine Yutaka <ebine.yutaka@sabel.jp>
 * @copyright  2004-2008 Mori Reo <mori.reo@sabel.jp>
 * @license    http://www.opensource.org/licenses/bsd-license.php  BSD License
 */
class Sabel_Xml_Element extends Sabel_Object
{
  /**
   * @var string
   */
  public $tagName = "";
  
  /**
   * @var DOMNode
   */
  protected $element = null;
  
  public function __construct(DOMNode $element)
  {
    $this->element = $element;
    
    switch ($element->nodeType) {
      case XML_ELEMENT_NODE:
        $this->tagName = $element->tagName;
        break;
      
      case XML_ELEMENT_NODE:
        $this->tagName = "#text";
        break;
        
      case XML_CDATA_SECTION_NODE:
        $this->tagName = "#cdata";
        break;
    }
  }
  
  public function __toString()
  {
    return $this->getValue();
  }
  
  public function getRawDocument()
  {
    return $this->element->ownerDocument;
  }
  
  public function getRawElement()
  {
    return $this->element;
  }
  
  public function getType()
  {
    return $this->element->nodeType;
  }
  
  public function reproduce()
  {
    return new self($this->element->cloneNode(true));
  }
  
  public function setNodeValue($value, $cdata = false)
  {
    if ($cdata) {
      $this->removeChildren();
      $this->appendChild(new self($this->getRawDocument()->createCDATASection($value)));
    } else {
      $this->element->nodeValue = $value;
    }
    
    return $this;
  }
  
  public function getNodeValue()
  {
    return $this->element->nodeValue;
  }
  
  public function setValue($value)
  {
    return $this->setNodeValue($value);
  }
  
  public function getValue()
  {
    return $this->getNodeValue();
  }
  
  public function getInnerContent()
  {
    $content = "";
    
    foreach ($this->getRawElement()->childNodes as $child) {
      $doc = new DOMDocument();
      $doc->appendChild($doc->importNode($child, true));
      $content .= trim($doc->saveHTML());
    }
    
    return $content;
  }
  
  public function setAttribute($name, $value)
  {
    $this->element->setAttribute($name, $value);
    
    return $this;
  }
  
  public function getAttribute($name)
  {
    return $this->element->getAttribute($name);
  }
  
  public function getAttributes()
  {
    return new Sabel_Xml_Attributes($this->element->attributes);
  }
  
  public function at($name, $value = null)
  {
    $c = func_num_args();
    if ($c === 1) {
      return $this->getAttribute($name);
    } else {
      if ($value === null) $value = "";
      return $this->setAttribute($name, $value);
    }
  }
  
  public function hasAttribute($name)
  {
    return $this->element->hasAttribute($name);
  }
  
  public function appendChild($element)
  {
    if ($element instanceof self) {
      $element = $element->getRawElement();
    }
    
    $this->element->appendChild($element);
    
    return $this;
  }
  
  public function insertBefore($element, $ref = null)
  {
    if ($element instanceof self) {
      $element = $element->getRawElement();
    }
    
    if (empty($ref)) {
      $this->getRawElement()->appendChild($element);
    } else {
      if ($ref instanceof self) {
        $this->getRawElement()->insertBefore($element, $ref->getRawElement());
      } else {
        $this->getRawElement()->insertBefore($element, $ref);
      }
    }
  }
  
  public function insertAfter($element, $ref = null)
  {
    if ($element instanceof self) {
      $element = $element->getRawElement();
    }
    
    if (empty($ref)) {
      $this->getRawElement()->appendChild($element);
    } else {
      $nextSibling = null;
      
      if ($ref instanceof self) {
        if (($_elem = $ref->getNextSibling()) !== null) {
          $nextSibling = $_elem->getRawElement();
        }
      } else {
        $_element = $ref;
        
        while (true) {
          if (($_element = $_element->nextSibling) === null) {
            break;
          }
          
          if ($_element->nodeType === XML_ELEMENT_NODE) {
            $nextSibling = $_element;
            break;
          }
        }
      }
      
      if ($nextSibling === null) {
        $this->getRawElement()->appendChild($element);
      } else {
        $this->getRawElement()->insertBefore($element, $nextSibling);
      }
    }
  }
  
  public function insertPreviousSibling($element)
  {
    if ($element instanceof self) {
      $element = $element->getRawElement();
    }
    
    $parent = $this->getParent()->getRawElement();
    $parent->insertBefore($element, $this->element);
    
    return $this;
  }
  
  public function insertNextSibling($element)
  {
    if ($next = $this->getNextSibling()) {
      $next->insertPreviousSibling($element);
    } else {
      $parent = $this->getParent();
      $parent->appendChild($element);
    }
    
    return $this;
  }
  
  public function __get($tagName)
  {
    return $this->getChildren($tagName);
  }
  
  public function __isset($tagName)
  {
    return ($this->getChildren($tagName)->length > 0);
  }
  
  public function xpath($query)
  {
    $nodes = $this->getRawDocument()->xpath->evaluate($query, $this->element);
    
    $elements = array();
    if ($nodes->length > 0) {
      for ($i = 0, $c = $nodes->length; $i < $c; $i++) {
        $element = $nodes->item($i);
        if ($element->nodeType === XML_ELEMENT_NODE) {
          $elements[] = new self($element);
        }
      }
    }
    
    return new Sabel_Xml_Elements($elements);
  }
  
  public function select($query)
  {
    $_exp = explode(" ", trim($query));
    
    if ($_exp[1] === ".") {
      $target = "../" . $this->tagName;
    } else {
      $target = str_replace(".", "/", $_exp[1]);
    }
    
    unset($_exp[0]);
    unset($_exp[1]);
    unset($_exp[2]);
    
    if (empty($_exp)) {
      return $this->xpath($target);
    } else {
      return $this->xpath($target . "[" . Sabel_Xml_Query::toXpath(implode(" ", $_exp)) . "]");
    }
  }
  
  public function delete($query)
  {
    $elements = $this->select($query);
    
    if ($elements->length > 0) {
      foreach ($elements as $element) {
        $element->remove();
      }
    }
  }
  
  public function addText($text, $cdata = false)
  {
    if ($cdata) {
      $_element = $this->getRawDocument()->createCDATASection($text);
    } else {
      $_element = $this->getRawDocument()->createTextNode($text);
    }
    
    $this->appendChild($_element);
    
    return $this;
  }
  
  public function addChild($tagName, $value = null)
  {
    if ($value === null) {
      $_element = $this->getRawDocument()->createElement($tagName);
    } else {
      $_element = $this->getRawDocument()->createElement($tagName, $value);
    }
    
    $this->appendChild($_element);
    return new self($_element);
  }
  
  public function getChild($tagName, $namespaceUri = null)
  {
    return $this->getChildren($tagName, $namespaceUri)->getElementAt(0);
  }
  
  public function getChildren($tagName = null, $namespaceUri = null)
  {
    if ($tagName === null) {
      $elements = array();
      $nodes = $this->element->childNodes;
      if ($nodes->length > 0) {
        for ($i = 0, $c = $nodes->length; $i < $c; $i++) {
          $element = $nodes->item($i);
          if ($element->nodeType === XML_ELEMENT_NODE) {
            $elements[] = new self($element);
          }
        }
      }
      
      return new Sabel_Xml_Elements($elements);
    } else {
      $element = $this->element;
      $namespaces = $this->getRawDocument()->defaultNamespaces;
      
      if ($namespaceUri !== null) {
        if (isset($namespaces[$namespaceUri])) {
          $tagName = $namespaces[$namespaceUri] . ":" . $tagName;
        }
      } elseif (strpos($tagName, ":") === false && $element->namespaceURI !== null) {
        if ($element->prefix === "") {  // default namespace
          if (isset($namespaces[$element->namespaceURI])) {
            $tagName = $namespaces[$element->namespaceURI] . ":" . $tagName;
          }
        } else {
          $tagName = $element->prefix . ":" . $tagName;
        }
      }
      
      return $this->xpath($tagName);
    }
  }
  
  public function removeChildren($ignoreTextNode = false)
  {
    if ($ignoreTextNode) {
      foreach ($this->getChildren() as $child) {
        $child->remove();
      }
    } else {
      $element = $this->element;
      
      $children = array();
      foreach ($element->childNodes as $child) {
        $children[] = $child;
      }
      
      foreach ($children as $child) {
        $element->removeChild($child);
      }
    }
    
    return $this;
  }
  
  public function getParent($target = null)
  {
    if ($target === null) {
      $parent = $this->element->parentNode;
      if ($parent->nodeType === XML_DOCUMENT_NODE) {
        return null;
      } else {
        return new self($parent);
      }
    } else {
      $element = $this;
      while (true) {
        if (($element = $element->getParent()) === null) {
          return null;
        } elseif ($element->tagName === $target) {
          return $element;
        }
      }
    }
  }
  
  public function getFirstChild()
  {
    if (($firstChild = $this->element->firstChild) === null) {
      return null;
    } elseif ($firstChild->nodeType === XML_ELEMENT_NODE) {
      return new self($firstChild);
    } else {
      $_firstChild = new self($firstChild);
      return $_firstChild->getNextSibling();
    }
  }
  
  public function getLastChild()
  {
    if (($lastChild = $this->element->lastChild) === null) {
      return null;
    } elseif ($lastChild->nodeType === XML_ELEMENT_NODE) {
      return new self($lastChild);
    } else {
      $_lastChild = new self($lastChild);
      return $_lastChild->getPreviousSibling();
    }
  }
  
  public function getPreviousSibling($target = null)
  {
    $element = $this->element;
    
    while (true) {
      if (($element = $element->previousSibling) === null) {
        return null;
      } elseif ($element->nodeType === XML_ELEMENT_NODE) {
        if ($target === null || $element->tagName === $target) {
          return new self($element);
        }
      }
    }
  }
  
  public function getPreviousSiblings()
  {
    $elements = array();
    $element  = $this;
    
    while (true) {
      if (($element = $element->getPreviousSibling()) === null) {
        break;
      } else {
        $elements[] = $element;
      }
    }
    
    return new Sabel_Xml_Elements($elements);
  }
  
  public function getNextSibling($target = null)
  {
    $element = $this->element;
    
    while (true) {
      if (($element = $element->nextSibling) === null) {
        return null;
      } elseif ($element->nodeType === XML_ELEMENT_NODE) {
        if ($target === null || $element->tagName === $target) {
          return new self($element);
        }
      }
    }
  }
  
  public function getNextSiblings()
  {
    $elements = array();
    $element  = $this;
    
    while (true) {
      if (($element = $element->getNextSibling()) === null) {
        break;
      } else {
        $elements[] = $element;
      }
    }
    
    return new Sabel_Xml_Elements($elements);
  }
  
  public function getSiblings()
  {
    return new Sabel_Xml_Elements(array_merge(
      $this->getPreviousSiblings()->reverse()->getRawElements(),
      $this->getNextSiblings()->getRawElements()
    ));
  }
  
  public function remove()
  {
    $parent = $this->getParent();
    
    if ($element = $parent->getRawElement()->removeChild($this->element)) {
      return new self($element);
    } else {
      return null;
    }
  }
  
  public function swap($element)
  {
    if (!$element instanceof self) {
      $element = new self($element);
    }
    
    $_self = $this->reproduce();
    $_elem = $element->reproduce();
    $this->insertPreviousSibling($_self);
    $element->insertPreviousSibling($_elem);
    
    $parent = $this->getParent()->getRawElement();
    $parent->replaceChild($element->getRawElement(), $this->getRawElement());
    $parent->replaceChild($_self->getRawElement(), $_elem->getRawElement());
  }
}
