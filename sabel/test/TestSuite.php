<?php

/**
 * Sabel_Test_TestSuite
 *
 * @category   Test
 * @package    org.sabel.test
 * @author     Ebine Yutaka <yutaka@ebine.org>
 * @copyright  2004-2008 Mori Reo <mori.reo@sabel.jp>
 * @license    http://www.opensource.org/licenses/bsd-license.php  BSD License
 */
class Sabel_Test_TestSuite extends PHPUnit_Framework_TestSuite
{
  public function setUp()
  {
    $this->doFixture("upFixture");
  }
  
  public function tearDown()
  {
    $this->doFixture("downFixture");
  }
  
  public function add($className)
  {
    $reflection = new ReflectionClass(new $className());
    
    if ($reflection->isSubClassOf("Sabel_Test_TestSuite")) {
      $this->addTest($reflection->getMethod("suite")->invoke(null));
    } else {
      $this->addTest(new self($className));
    }
  }
  
  protected function doFixture($method)
  {
    $name = ($this->name === "") ? get_class($this) : $this->name;
    
    $reflection = new Sabel_Reflection_Class($name);
    $annotation = $reflection->getAnnotation("fixture");
    
    if (isset($annotation[0])) {
      if ($method === "downFixture") {
        $annotation[0] = array_reverse($annotation[0]);
      }
      
      try {
        foreach ($annotation[0] as $fixture) {
          $className = "Fixture_" . $fixture;
          $fixture = new $className();
          $fixture->$method();
        }
      } catch (Exception $e) {
        if ($reflection->hasMethod($method . "Exception")) {
          $reflection->getMethod($method . "Exception")->invoke(null, $e);
        } else {
          throw $e;
        }
      }
    }
  }
}
