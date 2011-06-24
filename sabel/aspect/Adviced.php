<?php

/**
 * Adviced
 *
 * @category   aspect
 * @package    org.sabel.aspect
 * @author     Mori Reo <mori.reo@sabel.jp>
 * @copyright  2008-2011 Mori Reo <mori.reo@sabel.jp>
 * @license    http://www.opensource.org/licenses/bsd-license.php  BSD License
 */
class Sabel_Aspect_Adviced
{
  private $adviced = array();
  
  public function addAdvice($method, $advice)
  {
    $this->adviced[$method][] = $advice;
  }
  
  public function addAdvices($method, $advices)
  {
    if (isset($this->adviced[$method])) {
      if (is_array($this->adviced[$method])) {
        $this->adviced[$method] = array_merge($this->adviced[$method], $advices);
      } else {
        $this->adviced[$method] = array_merge(array($this->adviced[$method], $advices));
      }
    } else {
      $this->adviced[$method] = $advices;
    }
  }
  
  public function getAdvice($method)
  {
    if (isset($this->adviced[$method])) {
      return $this->adviced[$method];
    } else {
      return array();
    }
  }
  
  public function hasAdvice($method)
  {
    return isset($this->adviced[$method]);
  }
  
  public function getAllAdvie()
  {
    return $this->advied;
  }
  
  public function hasAdvices()
  {
    return (count($this->adviced) >= 1);
  }
  
  public function getAdvices()
  {
    return $this->adviced;
  }
}