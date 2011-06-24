<?php

/**
 * Sabel_Reflection_Property
 *
 * @category   Reflection
 * @package    org.sabel.reflection
 * @author     Ebine Yutaka <ebine.yutaka@sabel.jp>
 * @copyright  2004-2008 Mori Reo <mori.reo@sabel.jp>
 * @license    http://www.opensource.org/licenses/bsd-license.php  BSD License
 */
class Sabel_Reflection_Property extends ReflectionProperty
{
  private $annotations = false;
  
  public function getAnnotation($name)
  {
    $annotations = $this->getAnnotations();
    return (isset($annotations[$name])) ? $annotations[$name] : null;
  }
  
  public function getAnnotations()
  {
    if ($this->annotations === false) {
      $reader = Sabel_Annotation_Reader::create();
      $this->annotations = $reader->process($this->getDocComment());
    }
    
    return $this->annotations;
  }
  
  public function hasAnnotation($name)
  {
    $annotations = $this->getAnnotations();
    return isset($annotations[$name]);
  }
}
