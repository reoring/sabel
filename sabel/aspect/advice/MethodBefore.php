<?php

interface Sabel_Aspect_Advice_MethodBefore extends Sabel_Aspect_Advice
{
  public function before($method, $arguments, $target);
}