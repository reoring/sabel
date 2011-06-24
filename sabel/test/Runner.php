<?php

require_once ("PHPUnit/TextUI/TestRunner.php");

/**
 * Test Runner
 *
 * @category   Test
 * @package    org.sabel.test
 * @author     Mori Reo <mori.reo@sabel.jp>
 * @author     Ebine Yutaka <ebine.yutaka@sabel.jp>
 * @copyright  2004-2008 Mori Reo <mori.reo@sabel.jp>
 * @license    http://www.opensource.org/licenses/bsd-license.php  BSD License
 */
class Sabel_Test_Runner extends PHPUnit_TextUI_TestRunner
{
  private $classPrefix = "Unit_";
  
  public static function create()
  {
    return new self();
  }
  
  public function start($testName, $testFilePath)
  {
    if (is_readable($testFilePath)) {
      try {
        $testCaseName = $this->classPrefix . $testName;
        $this->doRun($this->getTest($testCaseName, $testFilePath));
      } catch (Exception $e) {
        Sabel_Console::error("couldn't run the TestSuite: " . $e->getMessage());
      }
    } else {
      Sabel_Console::error($testFilePath . " not found");
    }
  }
  
  public function setClassPrefix($prefix)
  {
    $this->classPrefix = $prefix;
  }
  
  public function getTest($suiteClassName, $suiteClassFile = "", $syntaxCheck = true)
  {
    Sabel::fileUsing($suiteClassFile, true);
    $testClass = new ReflectionClass($suiteClassName);
    
    if ($testClass->hasMethod(self::SUITE_METHODNAME)) {
      $suiteMethod = $testClass->getMethod(self::SUITE_METHODNAME);
      
      if (!$suiteMethod->isStatic()) {
        throw new Sabel_Exception_Runtime("suite() method must be static.");
      }
      
      try {
        $test = $suiteMethod->invoke(NULL, $testClass->getName());
      } catch (ReflectionException $e) {
        $message = sprintf("Failed to invoke suite() method.\n%s", $e->getMessage());
        throw new Sabel_Exception_Runtime($message);
      }
    } else {
      $test = new Sabel_Test_TestSuite($testClass);
    }
    
    $this->clearStatus();
    
    return $test;
  }
}
