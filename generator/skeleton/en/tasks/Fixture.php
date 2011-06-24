<?php

define("FIXTURE_DIR", RUN_BASE . DS . "tests" . DS . "fixture");

/**
 * Fixture
 *
 * @category   Sakle
 * @package    org.sabel.sakle
 * @author     Mori Reo <mori.reo@sabel.jp>
 * @copyright  2004-2008 Mori Reo <mori.reo@sabel.jp>
 * @license    http://www.opensource.org/licenses/bsd-license.php  BSD License
 */
class Fixture extends Sabel_Sakle_Task
{
  public function run()
  {
    if (count($this->arguments) < 2) {
      $this->usage();
      exit;
    }
    
    $method = $this->getFixtureMethod();
    $this->defineEnvironment($this->arguments[0]);
    Sabel_Db_Config::initialize(new Config_Database());
    
    if (Sabel_Console::hasOption("export", $this->arguments)) {
      unset($this->arguments[array_search("--export", $this->arguments, true)]);
      return $this->export("fixture");
    } elseif (Sabel_Console::hasOption("export-csv", $this->arguments)) {
      $dir = Sabel_Console::getOption("export-csv", $this->arguments);
      if ($dir === null) $dir = RUN_BASE . DS . "data";
      return $this->export("csv", $dir);
    }
    
    $fixtureName = $this->arguments[1];
    
    if ($fixtureName === "all") {
      foreach (scandir(FIXTURE_DIR) as $item) {
        if ($item === "." || $item === "..") continue;
        Sabel::fileUsing(FIXTURE_DIR . DS . $item, true);
        $className = "Fixture_" . substr($item, 0, strlen($item) - 4);
        $instance  = new $className();
        $instance->initialize();
        $instance->$method();
      }
    } else {
      $filePath = FIXTURE_DIR . DS . $fixtureName . ".php";
      if (Sabel::fileUsing($filePath, true)) {
        $className = "Fixture_" . $fixtureName;
        $instance  = new $className();
        $instance->initialize();
        $instance->$method();
        $this->success(ucfirst($method) . " " . $fixtureName);
      } else {
        $this->error("no such fixture file. '{$filePath}'");
      }
    }
  }
  
  protected function getFixtureMethod()
  {
    $method = "upFixture";
    $arguments = $this->arguments;
    
    if (Sabel_Console::hasOption("up", $arguments)) {
      $index = array_search("--up", $arguments, true);
      unset($arguments[$index]);
      $arguments = array_values($arguments);
    }
    
    if (Sabel_Console::hasOption("down", $arguments)) {
      $index = array_search("--down", $arguments, true);
      unset($arguments[$index]);
      $arguments = array_values($arguments);
      $method = "downFixture";
    }
    
    $this->arguments = $arguments;
    
    return $method;
  }
  
  public function usage()
  {
    echo "Usage: sakle Fixture [OPTION] ENVIRONMENT FIXTURE_NAME " . PHP_EOL;
    echo PHP_EOL;
    echo "  ENVIRONMENT:  production | test | development" . PHP_EOL;
    echo "  FIXTURE_NAME: fixture name or 'all'" . PHP_EOL;
    echo PHP_EOL;
    echo "  OPTION:" . PHP_EOL;
    echo "    --up      up fixture(default)" . PHP_EOL;
    echo "    --down    down fixture" . PHP_EOL;
    echo PHP_EOL;
    echo "Example: sakle Fixture development User" . PHP_EOL;
    echo PHP_EOL;
  }
  
  protected function export($type, $dir = null)
  {
    unset($this->arguments[0]);
    
    if ($type === "csv") {
      if (DS === "\\") {  // win
        if (preg_match('/^[a-z]:\\\\/i', $dir) === 0) {  // relative path
          $dir = RUN_BASE . DS . $dir;
        }
      } else {
        if ($dir{0} !== "/") {  // relative path
          $dir = RUN_BASE . DS . $dir;
        }
      }
      
      if (!is_dir($dir)) {
        $this->error("'{$dir}' is not directory.");
        exit;
      }
      
      $this->export_cvs($dir);
    } else {
      $this->export_fixture();
    }
  }
  
  protected function export_cvs($dir)
  {
    foreach ($this->arguments as $mdlName) {
      $fp = fopen($dir . DS . "{$mdlName}.csv", "w");
      foreach (MODEL($mdlName)->select() as $model) {
        fputcsv($fp, $model->toArray());
      }
      fclose($fp);
    }
  }
  
  protected function export_fixture()
  {
    foreach ($this->arguments as $mdlName) {
      $lines  = array();
      
      $code = array("<?php" . PHP_EOL);
      $code[] = "class Fixture_{$mdlName} extends Sabel_Test_Fixture";
      $code[] = "{";
      $code[] = "  public function upFixture()";
      $code[] = "  {";
      
      $models = MODEL($mdlName)->select();
      foreach ($models as $model) {
        $code[] = '    $this->insert(' . $this->createLine($model->toArray()) . ');';
      }
      
      $code[] = "  }" . PHP_EOL;
      $code[] = "  public function downFixture()";
      $code[] = "  {";
      $code[] = '    $this->deleteAll();';
      $code[] = "  }";
      $code[] = "}";
      
      $path = FIXTURE_DIR . DS . $mdlName . ".php";
      file_put_contents($path, implode(PHP_EOL, $code));
      
      $this->success("export $mdlName Records to '" . substr($path, strlen(RUN_BASE) + 1) . "'");
    }
  }
  
  protected function createLine($row)
  {
    $line = array();
    foreach ($row as $col => $val) {
      if (is_string($val)) {
        $val = '"' . str_replace('"', '\\"', $val) . '"';
      } elseif (is_bool($val)) {
        $val = ($val) ? "true" : "false";
      } elseif (is_null($val)) {
        $val = "null";
      }
      
      $line[] = "'{$col}' => $val";
    }
    
    return "array(" . implode(", ", $line) . ")";
  }
}
