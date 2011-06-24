<?php

class Sabel_Aspect_Introduction_DefaultAdvisor implements Sabel_Aspect_Introduction_Advisor
{
  private $advice = null;
  
  public function __construct($advice)
  {
    $this->advice = $advice;
  }
  
  public function getAdvice()
  {
    return $this->advice;
  }
  
  public function isPerInstance()
  {
    
  }
  
  public function getPointcut()
  {
    return new TrueMatchPointcut();
  }
}