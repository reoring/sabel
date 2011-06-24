<?php

class Functional_Index extends Sabel_Test_Functional
{
  public function testRequest()
  {
    $request = new Sabel_Request_Object();
    $request->get("index/index");
    $response = $this->request($request);
    $this->assertFalse($this->isRedirected($response));
  }
}
