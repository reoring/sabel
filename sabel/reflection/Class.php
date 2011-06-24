<?php

/**
 * Sabel_Reflection_Class
 *
 * @category   Reflection
 * @package    org.sabel.reflection
 * @author     Mori Reo <mori.reo@sabel.jp>
 * @author     Ebine Yutaka <ebine.yutaka@sabel.jp>
 * @copyright  2004-2008 Mori Reo <mori.reo@sabel.jp>
 * @license    http://www.opensource.org/licenses/bsd-license.php  BSD License
 */
class Sabel_Reflection_Class extends ReflectionClass
{
  protected $annotations = false;
  
  /**
   * get annotation of class
   *
   * @param string $name annotation name
   * @return string
   */
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
  
  public function getMethod($method)
  {
    return new Sabel_Reflection_Method($this->name, $method);
  }
  
  public function getMethods($filter = null)
  {
    if ($filter === null) {
      $filter = ReflectionMethod::IS_PUBLIC   | ReflectionMethod::IS_PROTECTED |
                ReflectionMethod::IS_PRIVATE  | ReflectionMethod::IS_STATIC    |
                ReflectionMethod::IS_ABSTRACT | ReflectionMethod::IS_FINAL;
    }
    
    $methods = array();
    foreach (parent::getMethods($filter) as $method) {
      $methods[$method->name] = $this->getMethod($method->name);
    }
    
    return $methods;
  }
  
  public function getMethodAnnotation($name, $annotationName)
  {
    return $this->getMethod($name)->getAnnotation($annotationName);
  }
  
  public function getProperty($property)
  {
    return new Sabel_Reflection_Property($this->name, $property);
  }
  
  public function getProperties($filter = null)
  {
    if ($filter === null) {
      $filter = ReflectionProperty::IS_PUBLIC  | ReflectionProperty::IS_PROTECTED |
                ReflectionProperty::IS_PRIVATE | ReflectionProperty::IS_STATIC;
    }
    
    $properties = array();
    foreach (parent::getProperties($filter) as $prop) {
      $properties[$prop->name] = $this->getProperty($prop->name);
    }
    
    return $properties;
  }
  
  public function isInstanciatable()
  {
    return (!$this->isInterface() && !$this->isAbstract());
  }
}
