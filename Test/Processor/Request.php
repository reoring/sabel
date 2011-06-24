<?php

/**
 * testcase for lib.processor.Request
 * using classes: sabel.Bus, sabel.request.Object
 *
 * @category  Processor
 * @author    Ebine Yutaka <ebine.yutaka@sabel.jp>
 */
class Test_Processor_Request extends Test_Processor_Abstract
{
  public static function suite()
  {
    Sabel::fileUsing(PROCESSORS_DIR . DS . "Request.php", true);
    return self::createSuite("Test_Processor_Request");
  }
  
  public function testProcess()
  {
    $bus = $this->bus;
    
    $this->assertFalse($bus->get("session")->isStarted());
    
    $processor = new Processor_Request("request");
    $processor->execute($bus);
    
    $this->assertTrue($bus->get("request") instanceof Sabel_Request);
    $this->assertNull($bus->get("request")->fetchPostValue("hoge"));
    $this->assertFalse($bus->get("session")->isStarted());
  }
  
  public function testSetRequestObject()
  {
    $bus = $this->bus;
    $request = new Sabel_Request_Object("");
    $request->setPostValue("hoge", "1");
    $bus->set("request", $request);
    
    $processor = new Processor_Request("request");
    $processor->execute($bus);
    
    $this->assertEquals("1", $bus->get("request")->fetchPostValue("hoge"));
  }
}
