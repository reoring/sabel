<?php

if (!defined("TEST_CASE")) {
  define("SAKLE", true);
  
  define("RUN_BASE", getcwd());
  require_once (dirname(__FILE__) . DIRECTORY_SEPARATOR . ".." .  DIRECTORY_SEPARATOR . "Sabel.php");
  require_once (RUN_BASE . DIRECTORY_SEPARATOR . "config" . DIRECTORY_SEPARATOR . "INIT.php");
  
  error_reporting(E_ALL|E_STRICT);
  define("SBL_LOG_LEVEL", SBL_LOG_ALL);
  
  if (isset($_SERVER["argv"][1])) {
    Sakle::run($_SERVER["argv"][1]);
  } else {
    Sabel_Console::error("empty task.");
  }
}

/**
 * Sakle
 *
 * @category   Sakle
 * @package    org.sabel.sakle
 * @author     Ebine Yutaka <ebine.yutaka@sabel.jp>
 * @copyright  2004-2008 Mori Reo <mori.reo@sabel.jp>
 * @license    http://www.opensource.org/licenses/bsd-license.php  BSD License
 */
class Sakle
{
  public static function run($class)
  {
    $args = $_SERVER["argv"];
    array_shift($args);
    
    $class = array_shift($args);
    unshift_include_path(RUN_BASE . DS . "tasks");
    
    if (class_exists($class, true)) {
      $ins = new $class();
      $ins->setArguments($args);
      
      if (isset($args[0]) && ($args[0] === "-h" || $args[0] === "--help")) {
        $ins->usage();
      } else {
        try {
          if ($ins->hasMethod("initialize")) {
            $ins->initialize();
          }
          
          $ins->run();
          
          if ($ins->hasMethod("finalize")) {
            $ins->finalize();
          }
        } catch (Exception $e) {
          Sabel_Console::error($e->getMessage());
        }
      }
    } else {
      Sabel_Console::error("such a task doesn't exist.");
    }
  }
}
