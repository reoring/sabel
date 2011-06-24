<?php

class Index_Controllers_Index extends Sabel_Controller_Page
{
  public function index()
  {
    $this->hoge = "10";
    $this->response->setResponse("fuga", "20");
  }
  
  public function hoge()
  {
    $this->name = "yamada";
  }
}
