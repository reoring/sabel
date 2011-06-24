<?php

/**
 * TestCase of sabel.aspect.*
 *
 * @author Mori Reo <mori.reo@sabel.jp>
 */
class Test_Aspect_Proxy extends SabelTestCase
{
  protected $weaver = null;
  
  public static function suite()
  {
    return self::createSuite("Test_Aspect_Proxy");
  }
  
  public function setUp()
  {
    $this->weaver = new Sabel_Aspect_Weaver("Sabel_Tests_Aspect_TargetClass");
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
  
  public function testNonExistTargetClass()
  {
    try {
      $this->weaver->setTarget("Non_Exist_Target_Class");
    } catch (Sabel_Exception_Runtime $e) {
      $this->assertTrue(true);
      return;
    }
    
    $this->fail();
  }
  
  public function testWeaverWithInterceptor()
  {
    $weaver = $this->weaver;
    
    $interceptor = new Sabel_Aspect_Interceptor_Debug();
    $advisor = new MyStaticMethodMatcherPointcutAdvisor();
    $advisor->setAdvice($interceptor);
    
    $weaver->addAdvisor($advisor);
    
    $interceptor = new Sabel_Aspect_Interceptor_SimpleTrace();
    $advisor = new MyStaticMethodMatcherPointcutAdvisor();
    $advisor->setAdvice($interceptor);
    
    $weaver->addAdvisor($advisor);
    
    $target = $weaver->getProxy();
    
    $result = $target->getX("arg", "arg2");
    $this->assertEquals("X", $result);
    
    $result = $target->getY("arg", "arg2");
    $this->assertEquals("Y", $result);
  }
  
  public function testAopWeaverWithoutInterceptors()
  {
    $weaver = $this->weaver;
    
    $target = $weaver->getProxy();
    
    $result = $target->getX("arg", "arg2");
    $this->assertEquals("X", $result);
    
    $result = $target->getY("arg", "arg2");
    $this->assertEquals("Y", $result);
  }
  
  public function testMultipleAdvice()
  {
    defineClass("ResultInterceptor", '
      class %s implements Sabel_Aspect_MethodInterceptor
      {
        public function invoke(Sabel_Aspect_MethodInvocation $invocation)
        {
          return "adviced " . $invocation->proceed();
        }
      }
    ');
    
    $weaver = $this->weaver;
    
    $weaver->setTarget("Sabel_Tests_Aspect_TargetClass");
    
    $advisor = new Sabel_Aspect_Advisor_RegexMatcherPointcut();
    $advisor->setClassMatchPattern("/.+/U");
    $advisor->setMethodMatchPattern("/get+/");
    
    $advisor->addAdvice(new ResultInterceptor());
    
    $weaver->addAdvisor($advisor);
    
    $target = $weaver->getProxy();
    
    $this->assertEquals("adviced X", $target->getX());
    $this->assertEquals("adviced Y", $target->getY());
    
    $advisor->addAdvice(new ResultInterceptor());
    
    $this->assertEquals("adviced adviced X", $weaver->getProxy()->getX());
    $this->assertEquals("adviced adviced Y", $weaver->getProxy()->getY());
    
    $advisor->addAdvice(new ResultInterceptor());
    
    $this->assertEquals("adviced adviced adviced X", $weaver->getProxy()->getX());
    $this->assertEquals("adviced adviced adviced Y", $weaver->getProxy()->getY());
  }
  
  public function testTargetMethodExistance()
  {
    $weaver = $this->weaver;
    
    $advisor = new Sabel_Aspect_Advisor_RegexMatcherPointcut();
    $advisor->setClassMatchPattern("/.+/U");
    $advisor->setMethodMatchPattern("/get+/");
    
    $beforeAdvice = new Sabel_Tests_Aspect_SimpleBeforeAdvice();
    $advisor->addAdvice(new Sabel_Tests_Aspect_SimpleAfterReturningAdvice());
    $advisor->addAdvice(new Sabel_Aspect_Interceptor_SimpleTrace());
    $advisor->addAdvice($beforeAdvice);
    
    $weaver->addAdvisor($advisor);
    
    $target = $weaver->getProxy();
    
    try {
      $target->invalidMethod();
      $this->assertTrue(false);
    } catch (Exception $e) {
      return;
    }
    
    $this->fail();
  }
  
  public function testSimpleBeforeAdvice()
  {
    $weaver = $this->weaver;
    
    $advisor = new Sabel_Aspect_Advisor_RegexMatcherPointcut();
    $advisor->setClassMatchPattern("/.+/U");
    $advisor->setMethodMatchPattern("/get+/");
    
    $beforeAdvice = new Sabel_Tests_Aspect_SimpleBeforeAdvice();
    $advisor->addAdvice(new Sabel_Tests_Aspect_SimpleAfterReturningAdvice());
    $advisor->addAdvice(new Sabel_Aspect_Interceptor_SimpleTrace());
    $advisor->addAdvice($beforeAdvice);
    
    $weaver->addAdvisor($advisor);
    
    $target = $weaver->getProxy();
    
    $target->getX();
    $target->getY();
    
    $this->assertEquals(array("getX", "getY"), $beforeAdvice->getCalledMethods());
  }
  
  public function testSimpleAfterAdvice()
  {
    $weaver = $this->weaver;
    
    $advisor = new Sabel_Aspect_Advisor_RegexMatcherPointcut();
    $advisor->setClassMatchPattern("/.+/U");
    $advisor->setMethodMatchPattern("/get+/");
    
    $advice = new Sabel_Tests_Aspect_SimpleAfterReturningAdvice();
    $advisor->addAdvice($advice);
    
    $weaver->addAdvisor($advisor);
    
    $target = $weaver->getProxy();
    
    $target->getX();
    $target->getY();
    
    $this->assertEquals(array("X", "Y"), $advice->getResults());
  }
  
  public function testSimpleThrowsAdvice()
  {
    $weaver = $this->weaver;
    
    $advisor = new Sabel_Aspect_Advisor_RegexMatcherPointcut();
    $advisor->setClassMatchPattern("/.+/U");
    $advisor->setMethodMatchPattern("/willThrowException/");
    
    $advice = new Sabel_Tests_Aspect_SimpleThrowsAdvice();
    $advisor->addAdvice($advice);
    
    $weaver->addAdvisor($advisor);
    
    $target = $weaver->getProxy();
    
    $this->assertEquals("", $advice->getThrowsMessage());
    
    try {
      $target->willThrowException();
    } catch (Exception $e) {
      $this->assertEquals("throws", $advice->getThrowsMessage());
    }
  }
  
  public function testSimpleThrowsAndReturnAdvice()
  {
    $weaver = $this->weaver;
    
    $advisor = new Sabel_Aspect_Advisor_RegexMatcherPointcut();
    $advisor->setClassMatchPattern("/.+/U");
    $advisor->setMethodMatchPattern("/.+/");
    
    $throwsAdvice    = new Sabel_Tests_Aspect_SimpleThrowsAdvice();
    $beforeAdvice    = new Sabel_Tests_Aspect_SimpleBeforeAdvice();
    $returningAdvice = new Sabel_Tests_Aspect_SimpleAfterReturningAdvice();
    
    $advisor->addAdvice($throwsAdvice);
    $advisor->addAdvice($returningAdvice);
    $advisor->addAdvice($beforeAdvice);
    
    $weaver->addAdvisor($advisor);
    
    $target = $weaver->getProxy();
    
    $this->assertEquals("", $throwsAdvice->getThrowsMessage());
    
    try {
      $target->willThrowException();
    } catch (Exception $e) {
      $this->assertEquals("throws", $throwsAdvice->getThrowsMessage());
      $this->assertEquals(array(), $returningAdvice->getResults());
      $this->assertEquals(array("willThrowException"), $beforeAdvice->getCalledMethods());
    }
  }
  
  public function testAdvices()
  {
    $advices = new Sabel_Aspect_Advices();
    $advices->addAdvice(new Sabel_Aspect_Interceptor_SimpleTrace());
    $advices->addAdvice(new Sabel_Tests_Aspect_SimpleBeforeAdvice());
    $advices->addAdvice(new Sabel_Tests_Aspect_SimpleAfterReturningAdvice());
    
    foreach ($advices->toArray() as $advice) {
      $this->assertTrue($advice instanceof Sabel_Aspect_Advice);
    }
  }
  
  public function testPlainObjectAdvice()
  {
    $weaver = $this->weaver;
    
    $advisor = new Sabel_Aspect_Advisor_RegexMatcherPointcut();
    $advisor->setClassMatchPattern("/.+/");
    $advisor->setMethodMatchPattern("/get+/");
    
    $throwAdvisor = new Sabel_Aspect_Advisor_RegexMatcherPointcut();
    $throwAdvisor->setClassMatchPattern("/.+/");
    $throwAdvisor->setMethodMatchPattern("/will+/");
    
    $poAdvice = new Sabel_Tests_Aspect_PlainObject_Advice();
    $plainObjectInterceptor = new Sabel_Aspect_Interceptor_PlainObjectAdvice($poAdvice);
    $plainObjectInterceptor->setBeforeAdviceMethod("before");
    $plainObjectInterceptor->setAfterAdviceMethod("after");
    $plainObjectInterceptor->setAroundAdviceMethod("around");
    $plainObjectInterceptor->setThrowsAdviceMethod("throws");
    
    $advisor->addAdvice($plainObjectInterceptor);
    $throwAdvisor->addAdvice($plainObjectInterceptor);
    
    $weaver->addAdvisor($advisor);
    $weaver->addAdvisor($throwAdvisor);
    
    $target = $weaver->getProxy();
    
    $target->getX();
    
    $this->assertEquals("getX", $poAdvice->before);
    $this->assertEquals("X", $poAdvice->after);
    
    $target->willThrowException();
    $this->assertEquals("throws", $poAdvice->throws);
  }
  
  public function testPlainObjectPreventBefore()
  {
    $weaver = $this->weaver;
    
    $advisor = new Sabel_Aspect_Advisor_RegexMatcherPointcut();
    $advisor->setClassMatchPattern("/.+/");
    $advisor->setMethodMatchPattern("/get+/");
    
    $poAdvice = new Sabel_Tests_Aspect_PlainObject_PreventBeforeAdvice();
    $plainObjectInterceptor = new Sabel_Aspect_Interceptor_PlainObjectAdvice($poAdvice);
    $plainObjectInterceptor->setBeforeAdviceMethod("before");
    
    $advisor->addAdvice($plainObjectInterceptor);
    
    $weaver->addAdvisor($advisor);
    
    $target = $weaver->getProxy();
    
    $result = $target->getX();
    
    $this->assertEquals("Y", $result);
  }
  
  public function testAnnotationPlainObjectAdvice()
  {
    $weaver = new Sabel_Aspect_Weaver();
    $weaver->build("Sabel_Tests_Aspect_TargetClass",
                   "Sabel_Tests_Aspect_PlainObject_Advice");
    
    $advice = $weaver->getAdvice();
    
    $target = $weaver->getProxy();
    
    $target->getX();
    $this->assertEquals("getX", $advice->before);
    
    $target->setX("x");
    $this->assertEquals("setX", $advice->before);
  }
  
  public function testGetClassName()
  {
    $weaver = $this->weaver;
    
    $weaver->setTarget("Sabel_Tests_Aspect_TargetClass");
    $target = $weaver->getProxy();
    
    $this->assertEquals($target->__getClassName(), "Sabel_Tests_Aspect_TargetClass");
  }
  
  public function testTargetClass()
  {
    $weaver = $this->weaver;
    
    $weaver->setTarget("Sabel_Tests_Aspect_TargetClass");
    $target = $weaver->getProxy();
    
    $this->assertEquals(get_class($target), "Sabel_Aspect_Proxy");
  }
}

/**
 * @classMatch Sabel+
 *
 * @advisor Sabel_Aspect_Advisor_RegexMatcherPointcut
 * @interceptor Sabel_Aspect_Interceptor_PlainObjectAdvice
 */
class Sabel_Tests_Aspect_PlainObject_Advice
{
  public
    $before,
    $after,
    $throws = "";
  
  /**
   * @before get+
   */
  public function before($method, $arguments, $target)
  {
    $this->before = $method->getName();
  }
  
  /**
   * @before set+
   */
  public function beforeSet($method, $arguments, $target)
  {
    $this->before = $method->getName();
  }
  
  public function after($method, $arguments, $target, $result)
  {
    $this->after = $result;
  }
  
  public function around($invocation)
  {
    $result = $invocation->proceed();
    return $result;
  }
  
  public function throws($method, $arguments, $target, $exception)
  {
    $this->throws = $exception->getMessage();
  }
}

class Sabel_Tests_Aspect_PlainObject_PreventBeforeAdvice
{
  public function before($method, $arguments, $target)
  {
    return "Y";
  }
}