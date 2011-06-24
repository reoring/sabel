<?php

/**
 * testcase for lib.processor.Router
 * using classes: sabel.Bus, sabel.Context, sabel.request.Object, sabel.map.*
 *
 * @category  Processor
 * @author    Ebine Yutaka <ebine.yutaka@sabel.jp>
 */
class Test_Processor_Router extends Test_Processor_Abstract
{
  public static function suite()
  {
    Sabel::fileUsing(PROCESSORS_DIR . DS . "Router.php", true);
    return self::createSuite("Test_Processor_Router");
  }
  
  public function testCreateDefaultCandidate()
  {
    $bus = $this->bus;
    $bus->set("request", new Sabel_Request_Object("index/test"));
    
    $processor = new Processor_Router("router");
    $processor->execute($bus);
    
    $candidate = Sabel_Context::getContext()->getCandidate();
    $this->assertTrue($candidate instanceof Sabel_Map_Candidate);
    $this->assertEquals("default", $candidate->getName());
  }
  
  public function testCreateDevelopmentCandidate()
  {
    $bus = $this->bus;
    $bus->set("request", new Sabel_Request_Object("devel/main/index/db"));
    
    $processor = new Processor_Router("router");
    $processor->execute($bus);
    
    $candidate = Sabel_Context::getContext()->getCandidate();
    $this->assertTrue($candidate instanceof Sabel_Map_Candidate);
    $this->assertEquals("devel", $candidate->getName());
    $this->assertEquals("db", $bus->get("request")->fetchParameterValue("param"));
  }
}
