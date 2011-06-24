<?php

/**
 * testcase for lib.processor.Addon
 *
 * @category  Processor
 * @author    Ebine Yutaka <ebine.yutaka@sabel.jp>
 */
class Test_Processor_Addon extends Test_Processor_Abstract
{
  public static function suite()
  {
    Sabel::fileUsing(PROCESSORS_DIR . DS . "Addon.php", true);
    return self::createSuite("Test_Processor_Addon");
  }
  
  public function testProcess()
  {
    $bus = $this->bus;
    
    $processor = new Processor_Addon("addon");
    $processor->execute($bus);
    
    $this->assertEquals(1, $bus->get("hogeAddon"));
    $this->assertEquals(2, $bus->get("fugaAddon"));
  }
}

class Hoge_Addon extends Sabel_Object
{
  public function execute($bus)
  {
    $bus->set("hogeAddon", 1);
  }
}

class Fuga_Addon extends Sabel_Object
{
  public function execute($bus)
  {
    $bus->set("fugaAddon", 2);
  }
}
