<?php

interface Sabel_Aspect_Advice_MethodAfterReturning extends Sabel_Aspect_Advice
{
  public function after($method, $arguments, $target, $returnValue);
}