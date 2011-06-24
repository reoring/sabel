<?php
 
/**
 * testcase of sabel.Bus, sabel.bus.*
 *
 * @category  Bus
 * @author    Mori Reo <mori.reo@sabel.jp>
 */
class Test_Bus_Runner extends SabelTestCase
{
  public static function suite()
  {
    return self::createSuite("Test_Bus_Runner");
  }
  
  public function testEmptyNameProcessor()
  {
    try {
      $bus = Sabel_Bus::create();
      $bus->addProcessor(new HogeProcessor(""));
    } catch (Sabel_Exception_InvalidArgument $e) {
      return;
    }
    
    $this->fail();
  }
  
  public function testProcessorList()
  {
    $bus = Sabel_Bus::create();
    $bus->addProcessor(new HogeProcessor("hoge"));
    $bus->addProcessor(new FugaProcessor("fuga"));
    $bus->addProcessor(new FooProcessor("foo"));
    
    $ins = $bus->getProcessor("hoge");
    $this->assertTrue($ins instanceof HogeProcessor);
    $this->assertNull($bus->getProcessor("test"));
    
    $list = $bus->getProcessorList();
    $this->assertTrue($list->has("hoge"));
    $this->assertTrue($list->has("fuga"));
    $this->assertTrue($list->has("foo"));
    $this->assertFalse($list->has("bar"));
  }
  
  public function testConfigs()
  {
    $bus = Sabel_Bus::create();
    eval ("class TemporaryConfig implements Sabel_Config
           {
             public function configure() {}
           }");
    
    $bus->setConfig("tmp", new TemporaryConfig());
    $this->assertTrue($bus->getConfig("tmp") instanceof Sabel_Config);
    $this->assertNull($bus->getConfig("hoge"));
  }
  
  public function testBusInit()
  {
    $bus = Sabel_Bus::create(array("null"   => null,
                                   "int"    => 10,
                                   "string" => "test",
                                   "bool"   => false));
    
    $this->assertEquals(null,   $bus->get("null"));
    $this->assertEquals(10,     $bus->get("int"));
    $this->assertEquals("test", $bus->get("string"));
    $this->assertEquals(false,  $bus->get("bool"));
  }
  
  public function testRun()
  {
    $bus = Sabel_Bus::create();
    $bus->run(new TestBusConfig());
    
    $this->assertEquals("10", $bus->get("a"));
    $this->assertEquals("20", $bus->get("b"));
    $this->assertEquals(null, $bus->get("c"));
  }
  
  public function testAttatchExecuteBeforeEvent()
  {
    $bus = Sabel_Bus::create();
    $bus->attachExecuteBeforeEvent("foo", new TestEvent(), "beforeMethod");
    $bus->run(new TestBusConfig());
    
    $this->assertEquals("before: fuga_result", $bus->get("beforeResult"));
  }
  
  public function testAttatchExecuteAfterEvent()
  {
    $bus = Sabel_Bus::create();
    $bus->attachExecuteAfterEvent("hoge", new TestEvent(), "afterMethod");
    $bus->run(new TestBusConfig());
    
    $this->assertEquals("after: hoge_result", $bus->get("afterResult"));
  }
  
  public function testAttatchExecuteAfterEvent2()
  {
    $bus = Sabel_Bus::create();
    $bus->attachExecuteAfterEvent("hoge", new TestEvent(),  "afterMethod");
    $bus->attachExecuteAfterEvent("hoge", new TestEvent2(), "afterMethod");
    $bus->run(new TestBusConfig());
    
    $this->assertEquals("after: hoge_result", $bus->get("afterResult"));
    $this->assertEquals("after: hoge_result", $bus->get("afterResult2"));
  }
  
  public function testHas()
  {
    $bus = Sabel_Bus::create();
    $bus->set("a", "10");
    $bus->set("b", "20");
    $bus->set("c", "30");
    
    $this->assertTrue($bus->has("a"));
    $this->assertFalse($bus->has("d"));
    
    $this->assertTrue($bus->has(array("a", "b", "c")));
    $this->assertFalse($bus->has(array("a", "d", "c")));
  }
}

class TestEvent
{
  public function beforeMethod($bus)
  {
    $bus->set("beforeResult", "before: " . $bus->get("result"));
  }
  
  public function afterMethod($bus)
  {
    $bus->set("afterResult", "after: " . $bus->get("result"));
  }
}

class TestEvent2
{
  public function afterMethod($bus)
  {
    $bus->set("afterResult2", "after: " . $bus->get("result"));
  }
}

class TestBusConfig extends Sabel_Bus_Config
{
  protected $processors = array("hoge" => "HogeProcessor",
                                "fuga" => "FugaProcessor",
                                "foo"  => "FooProcessor");
}

class HogeProcessor extends Sabel_Bus_Processor
{
  public function execute(Sabel_Bus $bus)
  {
    $bus->set("a", "10");
    $bus->set("result", "hoge_result");
  }
}

class FugaProcessor extends Sabel_Bus_Processor
{
  public function execute(Sabel_Bus $bus)
  {
    $bus->set("b", "20");
    $bus->set("result", "fuga_result");
  }
}

class FooProcessor extends Sabel_Bus_Processor
{
  public function execute(Sabel_Bus $bus)
  {
    $this->a = $bus->get("a");
    $this->b = $bus->get("b");
    
    if ($this->a !== "10") throw new Exception("test error");
    if ($this->b !== "20") throw new Exception("test error");
    
    $bus->set("result", "foo_result");
  }
}
