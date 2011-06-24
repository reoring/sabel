<?php

class Manage_Controllers_Index extends Sabel_Controller_Page
{
  public function index()
  {
    $param1 = $this->request->fetchParameterValue("param1");
    
    if (is_numeric($param1)) {
      $this->param1 = $param1;
      $this->param2 = $this->request->fetchParameterValue("param2");
    } else {
      $this->redirect->to("c: login, a: prepare");
    }
  }
  
  public function refuse()
  {
    throw new Exception("");
  }
}
