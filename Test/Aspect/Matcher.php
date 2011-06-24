<?php

/**
 * TestCase of sabel.aspect.*
 *
 * @author Mori Reo <mori.reo@sabel.jp>
 */
class Test_Aspect_Matcher extends SabelTestCase
{
  public static function suite()
  {
    return self::createSuite("Test_Aspect_Matcher");
  }
  
  public function testRegexMethodMatcher()
  {
    $matcher = new Sabel_Aspect_Matcher_RegexMethod();
    $matcher->setPattern("/set+/");
    $this->assertTrue($matcher->matches("setX", ""));
  }
  
  public function testRegexClassMatcher()
  {
    $matcher = new Sabel_Aspect_Matcher_RegexClass();
    $matcher->setPattern("/Sabel_+/");
    $this->assertTrue($matcher->matches("Sabel_Test", ""));
    
    $matcher->setPattern("/Sabel_+/");
    $this->assertFalse($matcher->matches("Test_Test", ""));
  }
}