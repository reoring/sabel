<?php

/**
 * Schema
 *
 * @category   Sakle
 * @package    org.sabel.sakle
 * @author     Ebine Yutaka <ebine.yutaka@sabel.jp>
 * @copyright  2004-2008 Mori Reo <mori.reo@sabel.jp>
 * @license    http://www.opensource.org/licenses/bsd-license.php BSD License
 */
class Schema extends Sabel_Sakle_Task
{
  public function run()
  {
    clearstatcache();
    $this->checkInputs();
    
    $outputDir = RUN_BASE . DS . LIB_DIR_NAME . DS . "schema";
    $this->defineEnvironment($this->arguments[0]);
    Sabel_Db_Config::initialize(new Config_Database());
    
    $isAll = false;
    $tables = $this->getOutputTables();
    if (isset($tables[0]) && strtolower($tables[0]) === "all") {
      $isAll = (count($tables) === 1);
    }
    
    $tList = new TableListWriter($outputDir);
    foreach (Sabel_Db_Config::get() as $connectionName => $params) {
      Sabel_Db_Config::add($connectionName, $params);
      $db = Sabel_Db::createMetadata($connectionName);
      $dbTables = $db->getTableList();
      
      if ($isAll) {
        foreach ($dbTables as $tblName) {
          $writer = new Sabel_Db_Metadata_FileWriter($outputDir);
          $writer->write($db->getTable($tblName));
          $this->success("output Schema 'Schema_" . convert_to_modelname($tblName) . "'");
          
          $tList->add($connectionName, $tblName);
        }
      } else {
        foreach ($tables as $tblName) {
          if (!in_array($tblName, $dbTables, true)) {
            $this->error("no such table: {$tblName}");
          } else {
            $writer = new Sabel_Db_Metadata_FileWriter($outputDir);
            $writer->write($db->getTable($tblName));
            $this->success("output Schema 'Schema_" . convert_to_modelname($tblName) . "'");
            
            $tList->add($connectionName, $tblName);
          }
        }
      }
      
      if (Sabel_Console::hasOption("l", $this->arguments)) {
        $tList->write($connectionName);
      }
    }
  }
  
  private function getOutputTables()
  {
    $args = $this->arguments;
    if (Sabel_Console::hasOption("t", $args)) {
      $tables = array();
      $idx = array_search("-t", $args, true);
      for ($i = ++$idx, $c = count($args); $i < $c; $i++) {
        if ($args[$i]{0} === "-") break;
        $tables[] = $args[$i];
      }
      
      return $tables;
    } else {
      return array();
    }
  }
  
  private function checkInputs()
  {
    $args = $this->arguments;
    
    if (count($args) < 2) {
      $this->usage();
      exit;
    } elseif ($args[0] === "--help" || $args[0] === "-h") {
      $this->usage();
      exit;
    }
  }
  
  public function usage()
  {
    echo "Usage: sakle Schema ENVIRONMENT [-l] -t TABLE1 TABLE2..." . PHP_EOL;
    echo PHP_EOL;
    echo "  ENVIRONMENT: production | test | development" . PHP_EOL;
    echo PHP_EOL;
    echo "  -l  output table list\n";
    echo "  -t  output metadata of table\n";
    echo PHP_EOL;
    echo "Example: sakle Schema production -l -t foo bar baz" . PHP_EOL;
    echo PHP_EOL;
  }
}

class TableListWriter
{
  private $tables = array();
  private $outputDir = "";
  
  public function __construct($outputDir)
  {
    if (is_dir($outputDir)) {
      $this->outputDir = $outputDir;
    } else {
      $message = "no such file or directory.";
      throw new Sabel_Exception_Runtime($message);
    }
  }
  
  public function add($connectionName, $tblName)
  {
    $this->tables[$connectionName][] = $tblName;
  }
  
  public function get($connectionName)
  {
    return $this->tables[$connectionName];
  }
  
  public function write($connectionName)
  {
    $cn        = $connectionName;
    $fileName  = ucfirst($cn) . "TableList";
    $className = "Schema_" . $fileName;
    
    Sabel_Console::success("output table list of '{$cn}' database.");
    
    $contents = array();
    $contents[] = "<?php" . PHP_EOL;
    $contents[] = "class $className";
    $contents[] = "{";
    $contents[] = "  public function get()";
    $contents[] = "  {";
    
    $tables = array_map(create_function('$v', 'return \'"\' . $v . \'"\';'), $this->tables[$cn]);
    
    $contents[] = "    return array(" . implode(", ", $tables) . ");";
    $contents[] = "  }";
    $contents[] = "}";
    
    $fp = fopen($this->outputDir . DS . $fileName . ".php", "w");
    fwrite($fp, implode(PHP_EOL, $contents));
    fclose($fp);
  }
}
