<?php

interface Sabel_Aspect_Pointcut_Regex extends Sabel_Aspect_Pointcut
{
  public function setClassMatchPattern($pattern);
  public function setMethodMatchPattern($pattern);
}