<?php

// require_once ("generator/skeleton/addon/flow/Processor.php");
// require_once ("generator/skeleton/addon/flow/State.php");
// require_once ("Test/Processor/classes/StandardFlow.php");

/**
 * TestCase of Processor_Flow
 *
 * @category   Test
 * @package    test.processor
 * @author     Mori Reo <mori.reo@gmail.com>
 * @copyright  2002-2006 Mori Reo <mori.reo@gmail.com>
 * @license    http://www.opensource.org/licenses/bsd-license.php  BSD License
 */
class Test_Processor_Flow extends SabelTestCase
{
  public static function suite()
  {
    return self::createSuite("Test_Processor_Flow");
  }
  
  private $bus = null;
  
  public function setUp()
  {
    $bus = new Sabel_Bus();
    
    $request     = new Sabel_Request_Object("index/index");    
    $storage     = new Sabel_Storage_InMemory();
    $controller  = new StandardFlow();
    $destination = new Sabel_Destination("index", "index", "top");
    
    $controller->setup($request, $destination, $storage);
    
    $bus->set("request",     $request);
    $bus->set("storage",     $storage);
    $bus->set("controller",  $controller);
    $bus->set("destination", $destination);
    
    $this->bus = $bus;
  }
  
  public function testStandardFlow()
  {
    $processor = new Processor_Flow("flow");
    $processor->setBus($this->bus);
    $processor->execute($this->bus);
  }
  
  public function testFailRequired()
  {
    $bus = new Sabel_Bus();
    $processor = new Processor_Flow("flow");
    $processor->setBus($this->bus);
    
    try {
      $processor->execute($bus);
      $this->fail();
    } catch (Sabel_Exception_Runtime $e) {
      $this->assertTrue(true);
    }
  }
}

class Config_Factory extends Sabel_Container_Injection
{
  public function configure()
  {
    $this->bind("Sabel_Response")->to("Sabel_Response_Web");
    $this->bind("Sabel_Locale")->to("Sabel_Locale_Null");
  }
}
