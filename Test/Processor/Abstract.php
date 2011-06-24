<?php

/**
 * abstract testcase for processor tests.
 * using classes: sabel.Bus, sabel.session.InMemory
 *
 * @category  Processor
 * @author    Ebine Yutaka <ebine.yutaka@sabel.jp>
 */
class Test_Processor_Abstract extends SabelTestCase
{
  protected $bus = null;
  
  public function setUp()
  {
    $this->bus = new Sabel_Bus();
    $this->bus->set("session", Sabel_Session_InMemory::create());
    $this->bus->setConfig("map",   new TestMapConfig());
    $this->bus->setConfig("addon", new TestAddonConfig());
  }
}
