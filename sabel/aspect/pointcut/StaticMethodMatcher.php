<?php

abstract class Sabel_Aspect_Pointcut_StaticMethodMatcher
             extends Sabel_Aspect_Matcher_StaticMethod
               implements Sabel_Aspect_Pointcut
{
  private $classMatcher = null;
  
  public function setClassMatcher(Sabel_Aspect_Matcher_Class $matcher)
  {
    $this->classMatcher = $matcher;
  }
  
  /**
   * implements from Pointcut interface
   */
  public function getClassMatcher()
  {
    return $this->classMatcher;
  }
  
  /**
   * implements from Pointcut interface
   */
  public function getMethodMatcher()
  {
    return $this;
  }
}