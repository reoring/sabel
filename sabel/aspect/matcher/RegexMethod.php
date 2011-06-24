<?php

class Sabel_Aspect_Matcher_RegexMethod implements Sabel_Aspect_Matcher_Method,
                                                 Sabel_Aspect_Matcher_Regex
{
  private $pattern = "";
  
  public function setPattern($pattern)
  {
    $this->pattern = $pattern;
  }
  
  public function matches($method, $class)
  {
    return (boolean) preg_match($this->pattern, $method);
  }
}