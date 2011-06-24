<?php

interface Sabel_Aspect_Advice_MethodThrows extends Sabel_Aspect_Advice
{
  public function throws($method, $arguments, $target, $exception);
}