<?php

/**
 * TestCase of sabel.aspect.pointcuts
 *
 * @author Mori Reo <mori.reo@sabel.jp>
 */
class Test_Aspect_Pointcuts extends SabelTestCase
{
  public static function suite()
  {
    return self::createSuite("Test_Aspect_Pointcuts");
  }
  
  protected $target    = null;
  protected $pointcuts = null;
  
  public function setUp()
  {
    $this->target = new Sabel_Tests_Aspect_TargetClass();
    $this->pointcuts = new Sabel_Aspect_DefaultPointcuts();
  }
  
  public function testPointcuts()
  {
    $match = $this->pointcuts->matches(new StaticPointcut(), "setX", $this->target);
    
    $this->assertTrue($match);
  }
  
  public function testRegexMatcherPointcuts()
  {
    $pointcuts = $this->pointcuts;
    $target = $this->target;
    
    
    $pointcut = new Sabel_Aspect_Pointcut_DefaultRegex();
    $pointcut->setClassMatchPattern("/Sabel+/");
    $pointcut->setMethodMatchPattern("/set+/");
    
    $match = $pointcuts->matches($pointcut, "setX", $target);
    $this->assertTrue($match);
    
    $match = $pointcuts->matches($pointcut, "setY", $target);
    $this->assertTrue($match);
    
    $match = $pointcuts->matches($pointcut, "getY", $target);
    $this->assertFalse($match);
  }
}