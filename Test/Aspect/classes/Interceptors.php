<?php

class Sabel_Tests_Aspect_SimpleBeforeAdvice implements Sabel_Aspect_Advice_MethodBefore
{
  private $calledMethods = array();
  
  public function before($method, $arguments, $target)
  {
    $this->calledMethods[] = $method->getName();
  }
  
  public function getCalledMethods()
  {
    return $this->calledMethods;
  }
}

class Sabel_Tests_Aspect_SimpleAfterReturningAdvice implements Sabel_Aspect_Advice_MethodAfterReturning
{
  private $results = array();
  
  public function after($method, $arguments, $target, $returnValue)
  {
    $this->results[] = $returnValue;
  }
  
  public function getResults()
  {
    return $this->results;
  }
}

class Sabel_Tests_Aspect_SimpleThrowsAdvice implements Sabel_Aspect_Advice_MethodThrows
{
  private $throwsMessage = "";
  
  public function throws($method, $arguments, $target, $exception)
  {
    $this->throwsMessage = $exception->getMessage();
  }
  
  public function getThrowsMessage()
  {
    return $this->throwsMessage;
  }
}