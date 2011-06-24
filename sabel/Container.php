<?php

/**
 * Sabel Container
 *
 * @category   Container
 * @package    org.sabel.container
 * @author     Mori Reo <mori.reo@sabel.jp>
 * @copyright  2004-2008 Mori Reo <mori.reo@sabel.jp>
 * @license    http://www.opensource.org/licenses/bsd-license.php  BSD License
 */
class Sabel_Container
{
  const SETTER_PREFIX  = "set";

  const INJECTION_ANNOTATION = "inject";

  private static $defaultConfig = null;

  /**
   * @var Sabel_Container_Injection
   */
  protected $config = null;

  /**
   * @var array reflection cache
   */
  protected $reflection = null;

  /**
   * @var array of dependency
   */
  protected $dependency = array();

  /**
   * @var array reflection cache
   */
  protected $reflectionCache = array();

  protected $instance = array();

  /**
   *
   * @param mixed $config object | string
   */
  public static function create($config = null)
  {
    if ($config === null) return new self();

    if (!$config instanceof Sabel_Container_Injection) {
      throw new Sabel_Exception_InvalidArgument("config must be a Sabel_Container_Injection");
    }

    return new self($config);
  }

  /**
   * create new instance with injection config
   *
   * @param string $className
   * @param mixed $config object | string
   */
  public static function load()
  {
    $args = func_get_args();

    $numArgs = count($args);

    if ($numArgs === 0) {
      throw new Sabel_Exception_InvalidArgument("must be specify target class");
    }

    if (is_array($args)) {
      $class = $args[0];
    } else {
      $class = $args;
    }

    $configs = array();

    if (self::hasDefaultConfig()) {
      $configs[] = self::getDefaultConfig();
    }

    if ($numArgs >= 2) {
      if ($args[1] instanceof Sabel_Container_Injection) {
        $configs[] = $args[1];
      }

      for ($i = 2; $i < $numArgs; ++$i) {
        if ($args[$i] instanceof Sabel_Container_Injection) {
          $configs[] = $args[$i];
        }
      }
    }

    if (count($configs) >= 2) {
      $compositeConfig = new Sabel_Container_CompositeConfig();

      foreach ($configs as $config) {
        $compositeConfig->add($config);
      }

      $compositeConfig->configure();

      $config = $compositeConfig;
    } else {
      if (isset($configs[0])) {
        $config = $configs[0];
      } else if (self::hasConfig("default")) {
        $config = self::getConfig("default");
      } else {
        $config = new Sabel_Container_DefaultInjection();
      }
    }

    if (!$config instanceof Sabel_Container_Injection) {
      throw new Sabel_Container_Exception_InvalidConfiguration("configuration not found");
    }

    return self::create($config)->newInstance($class);
  }

  /**
   * set default config
   *
   * @param Sabel_Container_Injection $config
   * @static
   * @access public
   * @return void
   * @throws Sabel_Container_Exception_InvalidConfiguration
   */
  public static function setDefaultConfig(Sabel_Container_Injection $config)
  {
    if (!$config instanceof Sabel_Container_Injection) {
      $msg = "object type must be Sabel_Container_Injection";
      throw new Sabel_Container_Exception_InvalidConfiguration($msg);
    }

    self::$defaultConfig = $config;
  }

  /**
   * has default config
   *
   * @param string $name
   * @return boolean
   */
  public static function hasDefaultConfig()
  {
    return (self::$defaultConfig !== null);
  }

  public static function getDefaultConfig()
  {
    return self::$defaultConfig;
  }

  /**
   * default constructer
   *
   * @param Sabel_Container_Injection $injection
   */
  public function __construct($config = null)
  {
    if ($config !== null) {
      $config->configure();
      $this->config = $config;
    }
  }

  /**
   * get new class instance from class name
   *
   * @param string $className
   * @return object
   */
  public function newInstance($class, $arguments = null)
  {
    $reflection = $this->getReflection($class);

    if (is_object($class)) {
      $instance = $class;
    } else {
      $className = $class;

      if ($reflection->isInstanciatable()) {
        if (is_array($arguments)) {
          $instance = $reflection->newInstanceArgs($constructArguments);
        } elseif (is_string($arguments)) {
          $instance = $reflection->newInstance($arguments);
        } else {
          $instance = $this->newInstanceWithConstruct($reflection, $className);
        }
      } else {
        $binds = $this->config->getBind($className);
        $bind  = (is_array($binds)) ? $binds[0] : $binds;

        $implementation = $bind->getImplementation();

        if ($this->config->hasConstruct($className)) {
          $instance = $this->newInstanceWithConstructInAbstract($className, $implementation);
        } elseif (is_array($arguments)) {
          $instance = $reflection->newInstanceArgs($constructArguments);
        } elseif (is_string($arguments)) {
          $instance = $reflection->newInstance($arguments);
        } else {
          $instance = $this->newInstance($implementation);
        }
      }
    }

    if ($reflection->hasMethod("setContainerContext")) {
      $instance->setContainerContext($this);
    }

    $instance = $this->injectToSetter($reflection, $instance);
    $instance = $this->applyAspect($instance);

    $this->instance[] = $instance;

    return $instance;
  }

  /**
   * inject to setter
   *
   * @param Sabel_Reflection_Class $reflection
   * @param Object $sourceInstance
   */
  protected function injectToSetter($reflection, $sourceInstance)
  {
    if (self::hasDefaultConfig()) {
      $defaultConfig = self::getDefaultConfig();
      $defaultConfig->configure();

      if ($defaultConfig->hasBinds()) {
        $this->processSetter($reflection, $sourceInstance, $defaultConfig);
      }
    }

    if ($this->config->hasBinds()) {
      $this->processSetter($reflection, $sourceInstance, $this->config);
    }

    return $sourceInstance;
  }

  private function processSetter($reflection, $sourceInstance, $config)
  {
    foreach ($config->getBinds() as $ifName => $binds) {
      foreach ($binds as $bind) {
        if ($bind->hasSetter()) {
          $injectionMethod = $bind->getSetter();
        } else {
          $injectionMethod = self::SETTER_PREFIX . ucfirst($ifName);
        }

        $implClassName = $bind->getImplementation();

        if (in_array($injectionMethod, get_class_methods($reflection->getName()), true)) {
            $argumentInstance = $this->newInstanceWithConstruct($reflection, $implClassName);

          if ($reflection->hasMethod($injectionMethod)) {
            $sourceInstance->$injectionMethod($argumentInstance);
          }
        } else {
          foreach ($reflection->getMethods() as $method) {
            $injectionMethod = $method->getName();

            $injection  = $method->getAnnotation(self::INJECTION_ANNOTATION);
            $parameters = $method->getParameters();

            if (isset($injection[0][0]) && $injection[0][0] === $ifName) {
              $argumentInstance = $this->newInstanceWithConstruct($reflection, $implClassName);
              $sourceInstance->$injectionMethod($argumentInstance);
            } elseif (isset($parameters[0]) && $injection !== null) {
              $parameter = $parameters[0];

              $parameterClass = $parameter->getClass();

              if ($parameterClass === null) {
                throw new Sabel_Container_Exception_InvalidConfiguration("must be type name specified");
              }

              if ($ifName === $parameterClass->getName()) {
                $argumentInstance = $this->newInstanceWithConstruct($reflection, $implClassName);
                $sourceInstance->$injectionMethod($argumentInstance);
              }
            }
          }
        }
      }
    }
  }

  protected function applyAspect($instance)
  {
    if ($instance === null) {
      throw new Sabel_Exception_Runtime("invalid instance " . var_export($instance, 1));
    }

    $reflection = $this->getReflection($instance);

    $resultInstance = $this->processAnnotatedAspect($instance, $reflection);

    if ($resultInstance !== null) {
      return $resultInstance;
    }

    $className = $reflection->getName();
    $adviceClasses = array();

    $aspects = $this->config->getAspects();

    if (count($aspects) === 0) {
      return $instance;
    }

    $interfaces = $reflection->getInterfaces();

    if (count($interfaces) >= 1) {
      foreach ($aspects as $aspect) {
        foreach ($interfaces as $implementInterface) {
          $implementName = $implementInterface->name;

          $parent = $aspect->getName();
          if ($implementName instanceof $parent || $aspect->getName() === $implementName) {
            $adviceClasses[] = $aspect->getAdvice();
          }
        }
      }
    } else {
      foreach ($aspects as $aspect) {
        $parent = $aspect->getName();

        if ($instance instanceof $parent) {
          $className = $aspect->getName();
          break;
        }
      }

      if (!$this->config->hasAspect($className)) return $instance;

      $adviceClasses[] = $this->config->getAspect($className)->getAdvice();
    }

    return Sabel_Aspect_Weaver::create()->build($instance, $adviceClasses)
                                        ->getProxy();
  }

  protected function processAnnotatedAspect($instance, $reflection)
  {
    $foundAnnotated = false;
    $aspects = $this->config->getAspects();

    foreach ($aspects as $aspect) {
      if (!$aspect->hasAnnotated()) {
        continue;
      }

      $interfaceName = $aspect->getName();

      if ($instance instanceof $interfaceName) {
        $annotated = $aspect->getAnnotated();
        $foundAnnotated = true;
        break;
      } elseif (preg_match("/$interfaceName/", $reflection->getName())) {
        $annotated = $aspect->getAnnotated();

        $foundAnnotated = true;
        break;
      }
    }

    if (!$foundAnnotated) {
      return null;
    }

    $weaver = new Sabel_Aspect_Weaver($instance);

    foreach ($reflection->getMethods() as $method) {
      $methodAnnots = $method->getAnnotations();

      foreach ($methodAnnots as $methodAnnotName => $v) {
        if (array_key_exists($methodAnnotName, $annotated)) {

          $advisor = new Sabel_Aspect_Advisor_RegexMatcherPointcut();
          $advisor->setClassMatchPattern("/" . $reflection->getName() . "/");
          $methodName = $method->getName();

          $interceptor = $annotated[$methodAnnotName][0];
          $advisor->setMethodMatchPattern("/" . $methodName . "/");

          $advisor->addAdvice(new $interceptor());
          $weaver->addAdvisor($advisor);
        }
      }
    }

    return $weaver->getProxy();
  }

  protected function newInstanceWithConstruct($reflection, $className)
  {
    if (is_object($className)) {
      return $className;
    }
    
    if (!$this->config->hasConstruct($reflection->getName())) {
      return $this->newInstanceWithConstructDependency($className);
    }

    $construct = $this->config->getConstruct($className);
    $constructArguments = array();

    foreach ($construct->getConstructs() as $constructValue) {
      if ($this->exists($constructValue)) {
        $instance = $this->constructInstance($constructValue);
        $constructArguments[] = $this->applyAspect($instance);
      } else {
        $constructArguments[] = $constructValue;
      }
    }

    return $reflection->newInstanceArgs($constructArguments);
  }

  protected function newInstanceWithConstructInAbstract($className, $implClass)
  {
    if (is_object($implClass)) {
      return $implClass;
    }
    
    if ($this->config->hasConstruct($className)) {
      $construct = $this->config->getConstruct($className);
      $constructArguments = array();

      foreach ($construct->getConstructs() as $constructValue) {
        if ($this->exists($constructValue)) {
          // @todo test this condition
          $instance = $this->constructInstance($constructValue);
          $constructArguments[] = $this->applyAspect($instance);
        } else {
          $constructArguments[] = $constructValue;
        }
      }

      $reflect  = $this->getReflection($implClass);
      $instance = $this->applyAspect($reflect->newInstanceArgs($constructArguments));

      return $instance;
    } else {
      return $this->applyAspect($this->newInstanceWithConstructDependency($className));
    }
  }

  /**
   * load instance of $className;
   *
   * @return object constructed instance
   */
  protected function newInstanceWithConstructDependency($className)
  {
    $this->scanDependency($className);
    $instance = $this->buildInstance();
    unset($this->dependency);
    $this->dependency = array();

    return $this->applyAspect($instance);
  }

  protected function constructInstance($className)
  {
    $reflection = $this->getReflection($className);

    if (!$reflection->isInterface()) {
      return $this->newInstance($className);
    }

    if ($this->config->hasBind($className)) {
      $bind = $this->config->getBind($className);

      if (is_array($bind)) {
        $implement = $bind[0]->getImplementation();
      } else {
        $implement = $bind->getImplementation();
      }

      return $this->newInstance($implement);
    } else {
      throw new Sabel_Exception_Runtime("any '{$className}' implementation not found");
    }
  }

  protected function exists($className)
  {
    return (Sabel::using($className) || interface_exists($className));
  }

  /**
   * scan dependency
   * 
   * @todo cycric dependency
   * @param string $class class name
   * @throws Sabel_Exception_Runtime when class does not exists
   */
  protected function scanDependency($className)
  {
    $constructerMethod = "__construct";

    if (!$this->exists($className)) {
      throw new Sabel_Container_Exception_UndefinedClass("{$className} does't exist");
    }

    $reflection = $this->getReflection($className);

    $this->dependency[] = $reflection;

    if (!$reflection->hasMethod($constructerMethod)) return $this;

    foreach ($reflection->getMethod($constructerMethod)->getParameters() as $parameter) {
      if (!$parameter->getClass()) continue;

      $dependClass = $parameter->getClass()->getName();

      if ($this->hasMoreDependency($dependClass)) {
        $this->scanDependency($dependClass);
      } else {
        $this->dependency[] = $this->getReflection($dependClass);
      }
    }

    return $this;
  }

  /**
   * @param string $class class name
   */
  protected function hasMoreDependency($class)
  {
    $constructerMethod = "__construct";

    $reflection = $this->getReflection($class);

    if ($reflection->isInterface() || $reflection->isAbstract()) return false;

    if ($reflection->hasMethod($constructerMethod)) {
      $refMethod = new ReflectionMethod($class, $constructerMethod);
      return (count($refMethod->getParameters()) !== 0);
    } else {
      return false;
    }
  }

  /**
   * construct an all depended classes
   *
   * @return object
   */
  protected function buildInstance()
  {
    $stackCount =(int) count($this->dependency);
    if ($stackCount < 1) {
      $msg = "invalid stack count";
      throw new Sabel_Exception_Runtime($msg);
    }

    $instance = null;

    for ($i = 0; $i < $stackCount; ++$i) {
      $reflection = array_pop($this->dependency);

      if ($reflection === null) continue;

      $className = $reflection->getName();

      if ($this->config->hasConstruct($className)) {
        $instance = $this->newInstance($className);
      } else {
        if ($reflection->isInstanciatable()) {
          $instance = $this->getInstance($className, $instance);
        } else {
          $instance = $this->newInstance($className);
        }
      }
    }

    return $instance;
  }

  /**
   * get instance of class name
   */
  protected function getInstance($className, $instance = null)
  {
    if (!$this->exists($className)) {
      throw new Sabel_Container_Exception_UndefinedClass("class {$clasName} does't exist");
    }

    if ($instance === null) {
      return new $className();
    } else {
      return new $className($instance);
    }
  }

  /**
   * get reflection class
   *
   */
  protected function getReflection($class)
  {
    if (is_object($class)) {
      $className = get_class($class);
    } else {
      $className = $class;
    }

    if (!isset($this->reflectionCache[$className])) {
      if (!$this->exists($className)) {
        throw new Sabel_Container_Exception_UndefinedClass("Class {$className} deos not exist");
      }

      $reflection = new Sabel_Reflection_Class($class);
      $this->reflectionCache[$className] = $reflection;

      return $reflection;
    }

    return $this->reflectionCache[$className];
  }

  private function getProperties($instance, $reflection)
  {
    $values = array();
    $properties = $reflection->getProperties();

    foreach ($properties as $property) {
      $pname = $property->getName();
      $getterMethod = "get" . ucfirst($pname);

      if ($reflection->hasMethod($getterMethod)) {
        $value = $instance->$getterMethod();
        $values[$pname] = $value;
      }
    }

    return $values;
  }
}
