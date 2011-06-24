<?php

/**
 * Sabel_Sakle_Task
 *
 * @category   Sakle
 * @package    org.sabel.sakle
 * @author     Ebine Yutaka <ebine.yutaka@sabel.jp>
 * @copyright  2004-2008 Mori Reo <mori.reo@sabel.jp>
 * @license    http://www.opensource.org/licenses/bsd-license.php  BSD License
 */
abstract class Sabel_Sakle_Task extends Sabel_Object
{
  protected $arguments = array();
  
  abstract public function run();
  
  public function setArguments($arguments)
  {
    $this->arguments = $arguments;
  }
  
  public function success($msg)
  {
    echo Sabel_Console::success($msg);
  }
  
  public function warning($msg)
  {
    echo Sabel_Console::warning($msg);
  }
  
  public function message($msg)
  {
    echo Sabel_Console::message($msg);
  }
  
  public function error($msg)
  {
    echo Sabel_Console::error($msg);
  }
  
  protected function defineEnvironment($strenv)
  {
    if (defined("ENVIRONMENT")) return;
    
    $env = @constant(strtoupper($strenv));
    
    if ($env === null) {
      $message = __METHOD__ . "() {$strenv} is not valid environment. "
               . "Use development, test or production.";
      
      throw new Sabel_Sakle_Exception($message);
    } else {
      define("ENVIRONMENT", $env);
    }
  }
  
  protected function defineEnvironmentByOption($opt = "e", $default = DEVELOPMENT)
  {
    if (Sabel_Console::hasOption($opt, $this->arguments)) {
      $this->defineEnvironment(Sabel_Console::getOption($opt, $this->arguments));
    } elseif (!defined("ENVIRONMENT")) {
      define("ENVIRONMENT", $default);
    }
  }
}
