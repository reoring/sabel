<?php

abstract class Sabel_Aspect_Pointcut_Abstract implements Sabel_Aspect_Pointcut
{
  protected $classMatcher  = null;
  protected $methodMatcher = null;
  
  public function setClassMatcher(Sabel_Aspect_ClassMatcher $matcher)
  {
    $this->classMatcher = $matcher;
  }
  
  public function setMethodMatcher(Sabel_Aspect_MethodMatcher $matcher)
  {
    $this->methodMatcher = $matcher;
  }
}