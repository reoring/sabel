<?php

class Sabel_Aspect_Advisor_RegexMatcherPointcut
    implements Sabel_Aspect_Pointcut_Advisor
{
  /**
   * @var Sabel_Aspect_Pointcut
   */
  private $pointcut = null;
  
  /**
   * @var Sabel_Aspect_Advice
   */
  private $advices = null;
  
  public function __construct()
  {
    $this->pointcut = new Sabel_Aspect_Pointcut_DefaultRegex();
    $this->advices  = new Sabel_Aspect_Advices();
  }
  
  public function setClassMatchPattern($pattern)
  {
    $this->pointcut->getClassMatcher()->setPattern($pattern);
  }
  
  public function setMethodMatchPattern($pattern)
  {
    $this->pointcut->getMethodMatcher()->setPattern($pattern);
  }
  
  public function addAdvice(Sabel_Aspect_Advice $advice)
  {
    $this->advices->addAdvice($advice);
  }
  
  /**
   * implements Sabel_Aspect_Pointcut_Advisor interface
   */
  public function getAdvice()
  {
    return $this->advices->toArray();
  }
  
  /**
   * implements Sabel_Aspect_Pointcut_Advisor interface
   */
  public function getPointcut()
  {
    return $this->pointcut;
  }
  
  /**
   * implements Sabel_Aspect_Pointcut_Advisor interface
   */
  public function isPerInstance()
  {
    
  }
}