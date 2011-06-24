<?php

/**
 * testcase for lib.processor.Response
 * using classes: sabel.controller.Page, sabel.response.Object
 *
 * @category  Processor
 * @author    Ebine Yutaka <ebine.yutaka@sabel.jp>
 */
class Test_Processor_Response extends Test_Processor_Abstract
{
  public static function suite()
  {
    Sabel::fileUsing(PROCESSORS_DIR . DS . "Response.php", true);
    return self::createSuite("Test_Processor_Response");
  }
  
  public function testProcess()
  {
    $bus = $this->bus;
    $response   = new Sabel_Response_Object();
    $response->setResponse("a", "1");
    $response->setResponse("b", "2");
    
    $controller = new ResponseTestController($response);
    $controller->setAttribute("b", "3");
    $controller->setAttribute("c", "4");
    
    $bus->set("controller", $controller);
    $bus->set("response", $response);
    
    $processor = new Processor_Response("response");
    $processor->afterAction($bus);
    
    $responses = $bus->get("response")->getResponses();
    $this->assertEquals("1", $responses["a"]);
    $this->assertEquals("3", $responses["b"]);
    $this->assertEquals("4", $responses["c"]);
    $this->assertFalse(isset($responses["d"]));
  }
}

class ResponseTestController extends Sabel_Controller_Page {}
