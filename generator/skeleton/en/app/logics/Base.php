<?php

class Logics_Base extends Sabel_Object
{
  public function createResult($value = null)
  {
    return new Logics_Result($value);
  }
  
  public function createResults()
  {
    return new Logics_Results();
  }
}
