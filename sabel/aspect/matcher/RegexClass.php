<?php

class Sabel_Aspect_Matcher_RegexClass implements Sabel_Aspect_Matcher_Class,
                                                Sabel_Aspect_Matcher_Regex
{
  private $pattern = "";
  
  public function setPattern($pattern)
  {
    $this->pattern = $pattern;
  }
  
  public function matches($class)
  {
    return (boolean) preg_match($this->pattern, $class);
  }
}