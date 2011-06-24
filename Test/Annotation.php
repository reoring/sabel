<?php

/**
 * testcase of sabel.annotation.Reader
 *
 * @category  Annotation
 * @author    Mori Reo <mori.reo@sabel.jp>
 */
class Test_Annotation extends SabelTestCase
{
  private $reader = null;
  
  public static function suite()
  {
    return self::createSuite("Test_Annotation");
  }
  
  public function setUp()
  {
    $this->reader = Sabel_Annotation_Reader::create();
  }
  
  public function testClassAnnotation()
  {
    $annotations = $this->reader->readClassAnnotation("TestAnnotation");
    $this->assertEquals("class", $annotations["annotation"][0][0]);
  }
  
  public function testBasic()
  {
    $annotation = $this->reader->readMethodAnnotation("TestAnnotation", "testMethod");
    $this->assertEquals("value", $annotation["param"][0][0]);
  }
  
  public function testMultipleValue()
  {
    $annotation = $this->reader->readMethodAnnotation("TestAnnotation", "testMethod");
    $this->assertEquals("hoge", $annotation["array"][0][0]);
    $this->assertEquals("fuga", $annotation["array"][0][1]);
  }
  
  public function testIgnoreSpace()
  {
    $annotation = $this->reader->readMethodAnnotation("TestAnnotation", "testMethod2");
    $this->assertEquals("value", $annotation["ignoreSpace"][0][0]);
  }
  
  public function testQuotedValue()
  {
    $annotation = $this->reader->readMethodAnnotation("TestAnnotation", "testMethod2");
    
    $this->assertEquals("hoge", $annotation["array"][0][0]);
    $this->assertEquals('  test"a"  ', $annotation["array"][0][1]);
    $this->assertEquals("a: index", $annotation["array"][0][2]);
    $this->assertEquals("fuga", $annotation["array"][1][0]);
    $this->assertEquals("  test'a'  ", $annotation["array"][1][1]);
    $this->assertEquals("c: index, a: index", $annotation["array"][1][2]);
  }
  
  public function testEmptyValue()
  {
    $annotation = $this->reader->readMethodAnnotation("TestAnnotation", "testMethod3");
    $this->assertTrue(isset($annotation["emptyValue"]));
    $this->assertNull($annotation["emptyValue"][0]);
  }
  
  public function testPropertyAnnotation()
  {
    $annotations = $this->reader->readPropertyAnnotation("TestAnnotation", "var");
    $this->assertEquals("int", $annotations["var"][0][0]);
  }
}

/**
 * class annotation
 *
 * @annotation class
 */
class TestAnnotation
{
  /**
   * @var int
   */
  protected $var = 10;
  
  /**
   * this is annotation test
   *
   * @param value
   * @array hoge fuga
   */
  public function testMethod($test, $test = null)
  {
    
  }
  
  /**
   * this is annotation test
   *
   * @ignoreSpace       value
   * @array hoge '  test"a"  ' "a: index"
   * @array fuga "  test'a'  " 'c: index, a: index'
   */
  public function testMethod2()
  {
    
  }
  
  /**
   * @emptyValue
   */
  public function testMethod3()
  {
    
  }
}
