<?php

/**
 * testcase for lib.processor.Controller
 * using classes: sabel.response.Object, sabel.map.Destination, sabel.controller.Page
 *
 * @category  Processor
 * @author    Ebine Yutaka <ebine.yutaka@sabel.jp>
 */
class Test_Processor_Controller extends Test_Processor_Abstract
{
  public static function suite()
  {
    Sabel::fileUsing(PROCESSORS_DIR . DS . "Controller.php", true);
    return self::createSuite("Test_Processor_Controller");
  }
  
  public function testHogeController()
  {
    $bus = $this->bus;
    $bus->set("response", new Sabel_Response_Object());
    $bus->set("destination", $this->getDestination("Hoge"));
    
    $processor = new Processor_Controller("controller");
    $processor->execute($bus);
    
    $controller = $bus->get("controller");
    $this->assertTrue($controller instanceof Test_Controllers_Hoge);
    $this->assertTrue($bus->get("response") instanceof Sabel_Response);
    $this->assertTrue($controller->getSession() instanceof Sabel_Session_Abstract);
  }
  
  public function testFugaController()
  {
    $bus = $this->bus;
    $bus->set("response", new Sabel_Response_Object());
    $bus->set("destination", $this->getDestination("Fuga"));
    
    $processor = new Processor_Controller("controller");
    $processor->execute($bus);
    
    $this->assertTrue($bus->get("controller") instanceof Test_Controllers_Fuga);
  }
  
  protected function getDestination($name)
  {
    return new Sabel_Map_Destination(array("module"     => "Test",
                                           "controller" => $name,
                                           "action"     => "index"));
  }
}

class Test_Controllers_Hoge extends Sabel_Controller_Page {}
class Test_Controllers_Fuga extends Sabel_Controller_Page {}
