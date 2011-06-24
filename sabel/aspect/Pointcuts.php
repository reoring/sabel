<?php

abstract class Sabel_Aspect_Pointcuts
{
  public function matches(Sabel_Aspect_Pointcut $pointcut, $method, $class)
  {
    $class = new Sabel_Reflection_Class($class);
    
    if ($pointcut === null) throw new Sabel_Exception_Runtime("pointcut can't be null");
    
    if ($pointcut->getClassMatcher()->matches($class)) {
      return $pointcut->getMethodMatcher()->matches($method, $class);
    }
    
    return false;
  }
}