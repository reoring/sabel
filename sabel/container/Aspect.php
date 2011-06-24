<?php

/**
 * Sabel Container Aspect
 *
 * @category   Container
 * @package    org.sabel.container
 * @author     Mori Reo <mori.reo@sabel.jp>
 * @copyright  2004-2008 Mori Reo <mori.reo@sabel.jp>
 * @license    http://www.opensource.org/licenses/bsd-license.php  BSD License
 */
final class Sabel_Container_Aspect
{
  private $className   = "";
  private $adviceClass = "";
  private $annotatedClasses = array();
  
  public function __construct($className)
  {
    $this->className = $className;
  }
  
  public function getName()
  {
    return $this->className;
  }
  
  public function advice($adviceClass)
  {
    $this->adviceClass = $adviceClass;
    return $this;
  }
  
  public function getAdvice()
  {
    return $this->adviceClass;
  }
  
  public function annotate($className, array $intercepters)
  {
    $this->annotatedClasses[$className] = $intercepters;
    return $this;
  }
  
  public function hasAnnotated()
  {
    return (count($this->annotatedClasses) >= 1);
  }
  
  public function getAnnotated()
  {
    return $this->annotatedClasses;
  }
}