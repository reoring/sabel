<?php


/**
 * TestCase of sabel container
 *
 * @category  Container
 * @author    Mori Reo <mori.reo@sabel.jp>
 */
class Test_Container extends SabelTestCase
{
  public static function suite()
  {
    return self::createSuite("Test_Container");
  }
  
  /**
   * setUp 
   * 
   * @access public
   * @return void
   */
  public function setUp()
  {
  }
  
  /**
   * @test
   */
  public function createContainer()
  {
    $container = Sabel_Container::create(new Sabel_CTest_Config());
  }
  
  /**
   * @test
   */
  public function createContainerWithInvalidConfiguration()
  {
    try {
      $container = Sabel_Container::create(new StdClass());
    } catch (Sabel_Exception_InvalidArgument $e) {
      $this->assertTrue(true);
      return;
    }
    
    $this->fail();
  }
  
  /**
   * @test
   */
  public function instanciateUndefinedClass()
  {
    $container = Sabel_Container::create(new Sabel_CTest_Config());
    
    try {
      $controller = $container->newInstance("Sabel_CTest_WillNotFoundClass");
    } catch (Sabel_Container_Exception_UndefinedClass $une) {
      $this->assertTrue(true);
      return;
    } catch (Exception $e) {
      echo $e->getMessage();
      $this->fail();
    }
    
    $this->fail();
  }
  
  /**
   * @test
   */
  public function injectionWithoutSetterConfig()
  {
  }
  
  public function injectionNestedDependency()
  {
  }
  
  /**
   * @test
   */
  public function injectionWithConstructor()
  {
    $container  = Sabel_Container::create(new Sabel_CTest_Config_Construct());
    $controller = $container->newInstance("Sabel_CTest_Controller_WithConstruct");
    $this->assertTrue($controller->model instanceof Sabel_CTest_Model);
  }
  
  /**
   * @test
   */
  public function SetterInjection()
  {
    $container  = Sabel_Container::create(new Sabel_CTest_Config());
    $controller = $container->newInstance("Sabel_CTest_Controller");
    $this->assertTrue($controller->model instanceof Sabel_CTest_Model);
  }
  
  /**
   * @test
   */
  public function constructorWithBind()
  {
    $container  = Sabel_Container::create(new Sabel_CTest_Config_Construct());
    $controller = $container->newInstance("Sabel_CTest_Controller_WithConstruct");
    $this->assertTrue($controller->model instanceof Sabel_CTest_Model);
  }
  
  /**
   * simple injection
   * 
   * @test
   */
  public function injection()
  {
    $injector = Sabel_Container::create(new Config());
    $person   = $injector->newInstance("Person");
    
    $this->assertEquals(10, $person->calc());
  }
  
  public function testConstructorInjection()
  {
    $injector = Sabel_Container::create(new ConstructConfig());
    $car = $injector->newInstance("Car");
    $this->assertTrue(is_object($car->getEngine()));
  }
  
  public function testStrLiteralConstructorInjection()
  {
    $injector = Sabel_Container::create(new StrLiteralConstructConfig());
    $car = $injector->newInstance("Car");
    $this->assertEquals("this is engine", $car->getEngine());
  }
  
  public function testNumLiteralConstructorInjection()
  {
    $injector = Sabel_Container::create(new NumLiteralConstructConfig());
    $car = $injector->newInstance("Car");
    $this->assertEquals(123, $car->getEngine());
  }
  
  public function testBoolLiteralConstructorInjection()
  {
    $injector = Sabel_Container::create(new BoolLiteralConstructConfig());
    $car = $injector->newInstance("Car");
    $this->assertTrue($car->getEngine());
  }
  
  public function testWrongInjectionConfig()
  {
    $injector = Sabel_Container::create(new WrongClassNameConfig());
    
    try {
      $person = $injector->newInstance("Person");
      $this->fail();
    } catch (Exception $e) {
      $this->assertEquals("WrongCalculator does't exist", $e->getMessage());
    }
  }
  
  public function testMultipleConstructerInjection()
  {
    $injector = Sabel_Container::create(new MultipleConstructConfig());
    
    $oil    = new EngineOil("normal");
    $engine = new MultiEngine($oil);
    $car    = new MultiCar($engine, "multiple");
    
    $injCar = $injector->newInstance("MultiCar");
    
    $this->assertEquals($car, $injCar);
  }
  
  public function testSpecificSetter()
  {
    $injector = Sabel_Container::create(new SpecificSetterConfig());
    $instance = $injector->newInstance("SpecificSetter");
    
    $engineOil = new EngineOil("specific");
    $specific  = new SpecificSetter();
    $specific->setSpecificSetter($engineOil);
    
    $this->assertEquals($instance, $specific);
  }
}

/** test classes **/

interface Sabel_CTest_Controller_Interface
{
}
class Sabel_CTest_Controller implements Sabel_CTest_Controller_Interface
{
  public $model = null;
  
  public function setModel(Sabel_CTest_Model $model)
  {
    $this->model = $model;
  }
}
class Sabel_CTest_Controller_WithConstruct
{
  public $model = null;

  public function __construct(Sabel_CTest_Model $model)
  {
    $this->model = $model;
  }
}
interface Sabel_CTest_Model
{
  public function getData();
}
interface Sabel_CTest_Result
{
}
class Sabel_CTest_Result_Implement implements Sabel_CTest_Result
{
}
class Sabel_CTest_Model_Implement implements Sabel_CTest_Model
{
  // @inject Sabel_CTest_Model
  public function getData()
  {
  }
}
class Person
{
  protected $age  = null;
  protected $calc = null;
  
  public function __construct(Age $age)
  {
    $this->age = $age;
  }
  
  public function howOldAreYou()
  {
    return $this->age->getAge();
  }
  
  public function setCalculator(Calculator $c)
  {
    $this->calc = $c;
  }
  
  public function getCalculator()
  {
    if (!is_object($this->calc)) {
      throw new Exception(var_export($this->calc, 1));
    }
    
    return $this->calc;
  }
  
  public function calc()
  {
    return $this->calc->calc($this);
  }
}
class Integer
{
  private $self = 0;
  
  public function set($num)
  {
    $this->self = $num;
  }
  
  public function add($num)
  {
    return $this->self + $num;
  }
}
interface Calculator
{
  public function calc($obj);
}
class FrastrationCalculator implements Calculator
{
  private $int = null;
  
  public function __construct(Integer $int)
  {
    $this->int = $int;
  }
  
  public function calc($obj)
  {
    $this->int->set(5);
    $this->int->add(5);
    return 10;
  }
}
class Age
{
  protected $age = 15;
  
  public function __construct()
  {
  }
  
  public function getAge()
  {
    return $this->age;
  }
  
  public function setAge($age = 0)
  {
    $this->age = $age;
  }
}
class SpecificSetter
{
  private $oil = null;
  public function setSpecificSetter(Oil $oil)
  {
    $this->oil = $oil;
  }
}
class MultiCar
{
  private $engine = null;
  private $shaft  = null;
  
  public function __construct($engine, $shaft)
  {
    $this->engine = $engine;
    $this->shaft  = $shaft;
  }
}
class MultiEngine
{
  private $oil = null;
  
  public function __construct($oil)
  {
    $this->oil = $oil;
  }
}
interface Oil
{
}
class EngineOil implements Oil
{
  private $type = "";
  public function __construct($type)
  {
    $this->type = $type;
  }
}
class Car
{
  private $engine = null;
  
  public function __construct($engine)
  {
    $this->engine = $engine;
  }
  
  public function getEngine()
  {
    return $this->engine;
  }
}
class Engine
{
  public function run(){}
}

class AspectConfig extends Sabel_Container_Injection
{
  private $trace = null;
  
  public function __construct($trace)
  {
    $this->trace = $trace;
  }
  
  public function configure()
  {
    $this->aspect("AspectTarget")->apply($this->trace)->to("run");
  }
  
  public function getTrace()
  {
    return $this->trace;
  }
}

class MultipleAspectConfig extends Sabel_Container_Injection
{
  public function configure()
  {
    $trace = new Trace();
    $this->aspect("AspectTarget")->apply($trace)->to("run");
    $this->aspect("AspectTarget")->apply($trace)->to("run");
  }
}

class AspectTarget
{
  public function run($parameter)
  {
    return $parameter;
  }
  
  public function runrun($parameter)
  {
    return $parameter;
  }
}

class BaseAspect
{
  public function after($joinpoint){}
  public function before($joinpoint){}
}

class Trace extends BaseAspect
{
  private $argument = "";
  
  public function after($joinpoint)
  {
    $this->argument = $joinpoint->getArgument(0);
  }
  
  public function getArgument()
  {
    return $this->argument;
  }
}

class Config extends Sabel_Container_Injection
{
  public function configure()
  {
    $this->bind("Calculator")->to("FrastrationCalculator");
  }
}
class WrongClassNameConfig extends Sabel_Container_Injection
{
  public function configure()
  {
    $this->bind("Calculator")->to("WrongCalculator");
  }
}
class AspectToMethod extends Sabel_Container_Injection
{
  public function configure()
  {
    $this->aspect("AspectTarget")->apply("Trace")->to("run");
  }
}
class AspectToEveryMethods extends Sabel_Container_Injection
{
  public function configure()
  {
    $this->aspect("AspectTarget")->apply("Trace")->toEveryMethods();
  }
}
class AspectToMethodRegex extends Sabel_Container_Injection
{
  public function configure()
  {
    $this->aspect("AspectTarget")->apply("Trace")->toMethodRegex("run.+");
  }
}
class ConstructConfig extends Sabel_Container_Injection
{
  public function configure()
  {
    $this->construct("Car")->with("Engine");
  }
}
class StrLiteralConstructConfig extends Sabel_Container_Injection
{
  public function configure()
  {
    $this->construct("Car")->with("this is engine");
  }
}
class NumLiteralConstructConfig extends Sabel_Container_Injection
{
  public function configure()
  {
    $this->construct("Car")->with(123);
  }
}
class BoolLiteralConstructConfig extends Sabel_Container_Injection
{
  public function configure()
  {
    $this->construct("Car")->with(true);
  }
}
class SpecificSetterConfig extends Sabel_Container_Injection
{
  public function configure()
  {
    $this->construct("EngineOil")->with("specific");
    $this->bind("Oil")->to("EngineOil")->setter("setSpecificSetter");
  }
}
class MultipleConstructConfig extends Sabel_Container_Injection
{
  public function configure()
  {
    $this->construct("MultiCar")->with("MultiEngine")
                                ->with("multiple");
                                    
    $this->construct("MultiEngine")->with("Oil");
    $this->construct("EngineOil")->with("normal");
    
    $this->bind("Oil")->to("EngineOil");
  }
}
class Sabel_CTest_Config extends Sabel_Container_Injection
{
  public function configure()
  {
    $this->bind("Sabel_CTest_Model")
         ->to("Sabel_CTest_Model_Implement")->setter("setModel");
  }
}
class Sabel_CTest_Config_WithoutSetter extends Sabel_Container_Injection
{
  public function configure()
  {
    $this->bind("Sabel_CTest_Model")
         ->to("Sabel_CTest_Model_Implement");
  }
}
class Sabel_CTest_Config_Construct extends Sabel_Container_Injection
{
  public function configure()
  {
    $this->construct("Sabel_CTest_Controller_WithConstruct")->with("Sabel_CTest_Model_Implement");
  }
}
class Sabel_CTest_Config_ConstructWithBind extends Sabel_Container_Injection
{
  public function configure()
  {
    $this->bind("Sabel_CTest_Model")->to("Sabel_CTest_Model_Implement");
    $this->construct("Sabel_CTest_Controller_WithConstruct")->with("Sabel_CTest_Model");
  }
}
class Sabel_CTest_Config_ConstructWithInvalidImplement extends Sabel_Container_Injection
{
  public function configure()
  {
    $this->construct("Sabel_CTest_Controller")->with("Sabel_CTest_Model");
  }
}
class Sabel_CTest_Config_ConstructWithInterface extends Sabel_Container_Injection
{
  public function configure()
  {
    $this->construct("Sabel_CTest_Controller")->with("Sabel_CTest_Model");
  }
}
