<?php

class Index_Controllers_Main extends Sabel_Controller_Page
{
  public function index()
  {
  }
  
  public function foo()
  {
    $internal = new Sabel_Request_Internal();
    $internal->values(array("pv" => "foo"));
    $internal->method(Sabel_Request::POST);
    $internal->request("main/bar", new AppBusConfig());
    $response  = $internal->getResponse();
    $this->bar = $response->getResponse("bar");
  }
  
  public function bar()
  {
    $foo = $this->request->fetchPostValue("pv");
    $this->bar = $foo . " bar";
  }
}
