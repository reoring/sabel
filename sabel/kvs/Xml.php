<?php

/**
 * @category   KVS
 * @package    org.sabel.kvs
 * @author     Ebine Yutaka <ebine.yutaka@sabel.php-framework.org>
 * @copyright  2004-2008 Mori Reo <mori.reo@sabel.jp>
 * @license    http://www.opensource.org/licenses/bsd-license.php  BSD License
 */
class Sabel_Kvs_Xml implements Sabel_Kvs_Interface
{
  private static $instances = array();
  
  protected $filePath = "";
  protected $lockFilePath = "";
  protected $lockfp = null;
  
  private function __construct($filePath)
  {
    $dir = dirname($filePath);
    
    if (!is_dir($dir)) {
      $message = __METHOD__ . "() no such directory '{$dir}'.";
      throw new Sabel_Exception_DirectoryNotFound($message);
    }
    
    $lockFilePath = $filePath . ".lock";
    
    if (!file_exists($lockFilePath)) {
      if (touch($lockFilePath)) {
        chmod($lockFilePath, 0777);
      } else {
        $message = __METHOD__ . "() can't create .lock file '{$lockFilePath}'.";
        throw new Sabel_Exception_Runtime($message);
      }
    }
    
    $this->filePath = $filePath;
    $this->lockFilePath = $lockFilePath;
    
    $this->lock();
    
    if (!file_exists($filePath)) {
      $xml = <<<XML
<?xml version="1.0" encoding="utf-8"?>
<values/>
XML;
      
      if (file_put_contents($filePath, $xml) === false) {
        $message = __METHOD__ . "() can't create file '{$filePath}'.";
        throw new Sabel_Exception_Runtime($message);
      } else {
        chmod($filePath, 0777);
      }
    }
    
    $this->unlock();
  }
  
  public static function create($filePath)
  {
    if (isset(self::$instances[$filePath])) {
      return self::$instances[$filePath];
    }
    
    return self::$instances[$filePath] = new self($filePath);
  }
  
  public function read($key)
  {
    $result = null;
    
    $this->lock();
    
    list ($doc, $element) = $this->getElement($key);
    
    if ($element) {
      $result = $this->_read($key, $doc, $element);
    }
    
    $this->unlock();
    
    if ($result === false || $result === "\000") {
      return null;
    } else {
      return $result;
    }
  }
  
  public function write($key, $value, $timeout = 0)
  {
    $this->lock();
    
    list ($doc, $element) = $this->getElement($key);
    
    if ($timeout !== 0) {
      $timeout = time() + $timeout;
    }
    
    $value = $doc->createCDATASection(
      str_replace("\000", "\\000", serialize($value))
    );
    
    if ($element === null) {
      $element = $doc->createElement($key);
      $element->setAttribute("timeout", $timeout);
      $element->appendChild($value);
      $doc->documentElement->appendChild($element);
    } else {
      $element->setAttribute("timeout", $timeout);
      $element->replaceChild($value, $element->firstChild);
    }
    
    $doc->save($this->filePath);
    
    $this->unlock();
  }
  
  public function delete($key)
  {
    $result = null;
    
    $this->lock();
    
    list ($doc, $element) = $this->getElement($key);
    
    if ($element) {
      $result = $this->_read($key, $doc, $element);
      
      if ($result === "\000") {
        $result = null;
      } else {
        $element->parentNode->removeChild($element);
        $doc->save($this->filePath);
      }
    }
    
    $this->unlock();
    
    return $result;
  }
  
  protected function _read($key, $doc, $element)
  {
    $result = null;
    
    if (($timeout = (int)$element->getAttribute("timeout")) === 0) {
      $result = $element->nodeValue;
    } else {
      if ($timeout <= time()) {
        $element->parentNode->removeChild($element);
        $doc->save($this->filePath);
        
        return "\000";
      } else {
        $result = $element->nodeValue;
      }
    }
    
    if ($result !== null) {
      $result = unserialize(str_replace("\\000", "\000", $result));
    }
    
    return ($result === false) ? null : $result;
  }
  
  protected function getElement($tagName)
  {
    $doc = new DOMDocument();
    $doc->load($this->filePath);
    
    return array($doc, $doc->documentElement->getElementsByTagName($tagName)->item(0));
  }
  
  protected function lock()
  {
    $fp = fopen($this->lockFilePath, "r");
    flock($fp, LOCK_EX);
    
    $this->lockfp = $fp;
  }
  
  protected function unlock()
  {
    if ($this->lockfp !== null) {
      fclose($this->lockfp);
      $this->lockfp = null;
    }
  }
}
