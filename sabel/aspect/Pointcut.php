<?php

/**
 * pointcut interface
 */
interface Sabel_Aspect_Pointcut
{
  /**
   * @return ClassMatcher
   */
  public function getClassMatcher();
  
  /**
   * @return MethodMatcher
   */
  public function getMethodMatcher();
}