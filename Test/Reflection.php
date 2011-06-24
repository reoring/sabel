<?php

/**
 * testcase for sabel.reflection.*
 *   & sabel.annotation.Reader
 *
 * @category Reflection
 * @author   Mori Reo <mori.reo@sabel.jp>
 */
class Test_Reflection extends SabelTestCase
{
  public static function suite()
  {
    return self::createSuite("Test_Reflection");
  }
    
  public function testReflection()
  {
    $o = new Vircle();
    $reflection = $o->getReflection();
    
    $this->assertTrue($reflection->hasAnnotation("hoge"));
    $this->assertTrue($reflection->hasAnnotation("class"));
    
    $annotation = $reflection->getAnnotation("class");
    $this->assertEquals("value", $annotation[0][0]);
  }
  
  public function testReflectionMethod()
  {
    $o = new Vircle();
    $reflection = $o->getReflection()->getMethod("fooMethod");
    
    $this->assertTrue($reflection->hasAnnotation("fuga"));
    $this->assertTrue($reflection->hasAnnotation("method"));
    
    $annotation = $reflection->getAnnotation("method");
    $this->assertEquals("value", $annotation[0][0]);
  }
  
  public function testMethodAnnotation()
  {
    $o = new Vircle();
    $annotation = $o->getReflection()->getMethodAnnotation("fooMethod", "method");
    $this->assertEquals("value", $annotation[0][0]);
  }
  
  public function testGetMethods()
  {
    $o = new Vircle();
    $methods = $o->getReflection()->getMethods();
    $annotations = $methods["fooMethod"]->getAnnotations();
    $this->assertEquals("value", $annotations["method"][0][0]);
  }
  
  public function testProperty()
  {
    $o = new Vircle();
    $hoge = $o->getReflection()->getProperty("hoge");
    $this->assertTrue($hoge->hasAnnotation("var"));
    $annotation = $hoge->getAnnotation("var");
    $this->assertEquals("string", $annotation[0][0]);
  }
  
  public function testGetProperties()
  {
    $o = new Vircle();
    $props = $o->getReflection()->getProperties();
    $annotations = $props["fuga"]->getAnnotations();
    $this->assertEquals("array", $annotations["var"][0][0]);
  }
}

/**
 * @hoge
 * @class value
 */
class Vircle extends Sabel_Object
{
  /**
   * @var string
   */
  protected $hoge = "";
  
  /**
   * @var array
   */
  protected $fuga = array();
  
  /**
   * @fuga
   * @method value
   */
  public function fooMethod()
  {
    
  }
}
