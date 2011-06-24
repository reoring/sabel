<?php

/**
 * Test_VirtualInheritance
 * 
 * @package Aspect
 * @author  Mori Reo <mori.reo@sabel.jp>
 */
class Test_VirtualInheritance extends SabelTestCase
{
  public static function suite()
  {
    return self::createSuite("Test_VirtualInheritance");
  }
  
  public function testVirtualInheritance()
  {
    $inherited = new Sabel_Aspect_VirtualInheritProxy(new InheritedClass());
    $inherited->inherit("VirtualParentClass")->inherit("VirtualParentClassTwo");
    
    $this->assertEquals("method",          $inherited->method());
    $this->assertEquals("parentMethod 1",    $inherited->parentMethod(1));
    $this->assertEquals("parentMethodTwo 1 2", $inherited->parentMethodTwo(1, 2));
  }
}

class InheritedClass
{
  public function method()
  {
    return "method";
  }
}

class VirtualParentClass
{
  public function parentMethod($arg)
  {
    return "parentMethod $arg";
  }
}

class VirtualParentClassTwo
{
  public function parentMethodTwo($arg1, $arg2)
  {
    return "parentMethodTwo $arg1 $arg2";
  }
}
