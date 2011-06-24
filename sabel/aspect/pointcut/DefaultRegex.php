<?php

class Sabel_Aspect_Pointcut_DefaultRegex implements Sabel_Aspect_Pointcut_Regex
{
  private $classMatcher = null;
  private $methodMatcher = null;
  
  public function __construct()
  {
    $this->classMatcher  = new Sabel_Aspect_Matcher_RegexClass();
    $this->methodMatcher = new Sabel_Aspect_Matcher_RegexMethod();
  }
  
  public function setClassMatchPattern($pattern)
  {
    $this->classMatcher->setPattern($pattern);
  }
  
  public function setMethodMatchPattern($pattern)
  {
    $this->methodMatcher->setPattern($pattern);
  }
  
  public function getClassMatcher()
  {
    return $this->classMatcher;
  }
  
  public function getMethodMatcher()
  {
    return $this->methodMatcher;
  }
}
