<?php

/**
 * TestCase of sabel.aspect.*
 *
 * @author Mori Reo <mori.reo@sabel.jp>
 */
class Test_Aspect_Introduction extends SabelTestCase
{
  public static function suite()
  {
    return self::createSuite("Test_Aspect_Introduction");
  }
  
  private $weaver = null;
  
  public function setUp()
  {
    $this->weaver = new Sabel_Aspect_Weaver("Sabel_Test_Aspect_Person");
  }
  
  public function testIntroduceLockable()
  {
    $mixin = new Sabel_Test_Aspect_LockMixin();
    
    $this->weaver->addAdvisor(new Sabel_Test_Aspect_LockMixinAdvisor($mixin));
    
    $person = $this->weaver->getProxy();
    $person->__checkTargetMethod(false);
    
    $person->lock();
    
    try {
      $person->setAge(28);
      $this->assertFalse(true);
    } catch (Sabel_Test_Aspect_LockedException $e) {
      $this->assertEquals("locked", $e->getMessage());
    }
    
    $person->unlock();
    $person->setAge(29);
    $this->assertEquals(29, $person->getAge());
  }
}

/**
 * target class
 */
class Sabel_Test_Aspect_Person
{
  private $age = 0;
  
  public function setAge($age)
  {
    $this->age = $age;
  }
  
  public function getAge()
  {
    return $this->age;
  }
}

/**
 * lockable interface
 */
interface Sabel_Test_Aspect_Lockable
{
  public function lock();
  public function unlock();
  public function locked();
}

class Sabel_Test_Aspect_LockMixin extends Sabel_Aspect_Introduction_DelegatingInterceptor
  implements Sabel_Test_Aspect_Lockable
{
  private $locked = false;
  
  public function lock()
  {
    $this->locked = true;
  }
  
  public function unlock()
  {
    $this->locked = false;
  }
  
  public function locked()
  {
    return $this->locked;
  }
  
  public function invoke(Sabel_Aspect_MethodInvocation $invocation)
  {
    $method = $invocation->getMethod()->getName();
    
    if (in_array($method, get_class_methods($this))) {
      $this->$method();
    }
    
    if (preg_match("/set+/", $invocation->getMethod()->getName())) {
      if ($this->locked()) {
        throw new Sabel_Test_Aspect_LockedException("locked");
      }
    }
    
    return parent::invoke($invocation);
  }
}

class Sabel_Test_Aspect_LockedException extends Sabel_Exception_Runtime {}


class Sabel_Test_Aspect_LockMixinAdvisor extends Sabel_Aspect_Introduction_DefaultAdvisor
{
}


class TrueClassMatcher implements Sabel_Aspect_Matcher_Class
{
  public function matches($class)
  {
    return true;
  }
}

class TrueMethodMatcher implements Sabel_Aspect_Matcher_Method
{
  public function matches($method, $class)
  {
    return true;
  }
}

class TrueMatchPointcut implements Sabel_Aspect_Pointcut
{
  public function getClassMatcher()
  {
    return new TrueClassMatcher();
  }
  
  public function getMethodMatcher()
  {
    return new TrueMethodMatcher();
  }
}
