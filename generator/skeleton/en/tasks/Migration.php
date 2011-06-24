<?php

/**
 * Migration
 *
 * @category   Sakle
 * @package    org.sabel.sakle
 * @author     Ebine Yutaka <ebine.yutaka@sabel.jp>
 * @copyright  2004-2008 Mori Reo <mori.reo@sabel.jp>
 * @license    http://www.opensource.org/licenses/bsd-license.php BSD License
 */
class Migration extends Sabel_Sakle_Task
{
  protected static $startVersion = null;
  protected static $versions = array();
  private static $execFinalize = true;
  
  protected $stmt      = null;
  protected $files     = array();
  protected $migrateTo = 0;
  protected $metadata  = null;
  
  protected $connectionName = "";
  protected $currentVersion = 0;
  
  public function run()
  {
    if (count($this->arguments) < 2) {
      $this->error("to few arguments.");
      $this->usage();
      exit;
    }
    
    $this->defineEnvironment($this->arguments[0]);
    
    Sabel_Db_Config::initialize(new Config_Database());
    $directory = $this->defineMigrationDirectory();
    
    $connectionName = $this->connectionName = $this->getConnectionName();
    $this->stmt     = Sabel_Db::createStatement($connectionName);
    $this->metadata = Sabel_Db::createMetadata($connectionName);
    
    // @todo
    //if ($this->arguments[1] === "export") {
    //  $this->export();
    //  self::$execFinalize = false;
    //} else {
      $tblName = $this->arguments[1];
      $this->currentVersion = $this->getCurrentVersion();
      if (in_array($tblName, array("-v", "--version"), true)) {
        $this->success("CURRENT VERSION: " . $this->currentVersion);
        exit;
      } else {
        $this->execMigration();
      }
    //}
  }
  
  protected function execMigration()
  {
    if (self::$startVersion === null) {
      self::$startVersion = $this->currentVersion;
    }
   
    $this->files = Sabel_Db_Migration_Manager::getFiles();
    
    if (empty($this->files)) {
      $this->error("No migration files is found.");
      exit;
    }
    
    if ($this->toVersionNumber($this->arguments[1]) !== false) {
      if ($this->_execMigration()) {
        $this->execNextMigration();
      }
    }
  }
  
  public function finalize()
  {
    if (!self::$execFinalize) return;
    
    $start = self::$startVersion;
    $end   = $this->getCurrentVersion();
    $mode  = ($start < $end) ? "UPGRADE" : "DOWNGRADE";
    
    $this->success("$mode FROM $start TO $end");
  }
  
  protected function getCurrentVersion()
  {
    Sabel_Db_Migration_Manager::setStatement($this->stmt);
    Sabel_Db_Migration_Manager::setMetadata($this->metadata);
    
    try {
      if (!in_array("sbl_version", $this->metadata->getTableList())) {
        $this->createVersioningTable();
        return 0;
      } else {
        return $this->getVersion();
      }
    } catch (Exception $e) {
      $this->error($e->getMessage());
      exit;
    }
  }
  
  protected function defineMigrationDirectory()
  {
    if (Sabel_Console::hasOption("d", $this->arguments)) {
      $dir = Sabel_Console::getOption("d", $this->arguments);
    } else {
      $dir = RUN_BASE . DS . "migration" . DS . $this->getConnectionName();
    }
    
    if (!is_dir($dir)) {
      $this->error("no such directory '{$dir}'.");
      exit;
    }
    
    Sabel_Db_Migration_Manager::setDirectory($dir);
    
    return $dir;
  }
  
  protected function _execMigration()
  {
    $version = $this->currentVersion;
    $to = (int)$this->migrateTo;
    
    if ((int)$version === $to) {
      $this->message("NO CHANGES FROM {$to}");
      exit;
    }
    
    $doNext = false;
    if ($version < $to) {
      $next   = $version + 1;
      $num    = $next;
      $mode   = "upgrade";
      $doNext = ($next < $to);
    } else {
      $next   = $version - 1;
      $num    = $version;
      $mode   = "downgrade";
      $doNext = ($next > $to);
    }
    
    Sabel_Db_Migration_Manager::setApplyMode($mode);
    
    $instance  = Sabel_Db::createMigrator($this->connectionName);
    $directory = Sabel_Db_Migration_Manager::getDirectory();
    $instance->execute($directory . DS . $this->files[$num]);
    $this->updateVersionNumber($next);
    
    return $doNext;
  }
  
  protected function execNextMigration()
  {
    $instance = new self();
    $instance->setArguments($this->arguments);
    $instance->run();
  }
  
  protected function toVersionNumber($to)
  {
    if (is_numeric($to)) {
      return $this->migrateTo = $to;
    }
    
    switch (strtolower($to)) {
      case "head":
        $this->migrateTo = max(array_keys($this->files));
        break;
      
      case "foot":
        $this->migrateTo = 0;
        break;
      
      case "rehead":
        $this->arguments[1] = 0;
        $this->execNextMigration();
        $this->success("DOWNGRADE FROM {$this->currentVersion} TO 0");
        $this->arguments[1] = "head";
        $this->execNextMigration();
        $this->success("UPGRADE FROM 0 TO " . $this->getCurrentVersion());
        $this->arguments[1] = "rehead";
        return self::$execFinalize = false;
      
      default:
        $this->error("version '{$to}' is not supported.");
        exit;
    }
  }
  
  protected function updateVersionNumber($num)
  {
    $stmt    = $this->stmt;
    $table   = $stmt->quoteIdentifier("sbl_version");
    $version = $stmt->quoteIdentifier("version");
    
    $stmt->setQuery("UPDATE $table SET $version = $num")->execute();
  }
  
  protected function getConnectionName()
  {
    $args = $this->arguments;
    return (isset($args[2])) ? $args[2] : "default";
  }
  
  protected function createVersioningTable()
  {
    $stmt      = $this->stmt;
    $sversion  = $stmt->quoteIdentifier("sbl_version");
    $version   = $stmt->quoteIdentifier("version");
    $createSql = "CREATE TABLE $sversion ({$version} INTEGER NOT NULL)";
    
    $stmt->setQuery($createSql)->execute();
    $stmt->setQuery("INSERT INTO {$sversion} VALUES(0)")->execute();
  }
  
  protected function getVersion()
  {
    $stmt    = $this->stmt;
    $table   = $stmt->quoteIdentifier("sbl_version");
    $version = $stmt->quoteIdentifier("version");
    
    $rows = $stmt->setQuery("SELECT $version FROM $table")->execute();
    return (isset($rows[0]["version"])) ? $rows[0]["version"] : 0;
  }
  
  protected function export()
  {
    $exporter = new MigrationExport($this->metadata, $this->connectionName);
    $exporter->export();
  }
  
  public function usage()
  {
    echo "Usage: sakle Migration ENVIRONMENT TO_VERSION [CONNECTION_NAME] " . PHP_EOL;
    echo PHP_EOL;
    echo "  ENVIRONMENT: production | test | development" . PHP_EOL;
    echo "  TO_VERSION:  number of target version | head | foot | rehead" . PHP_EOL;
    echo "  CONNECTION_NAME: " . PHP_EOL;
    echo PHP_EOL;
    echo "Example: sakle Migration development head userdb" . PHP_EOL;
    echo PHP_EOL;
  }
}

/*
class MigrationExport
{
  private $fileNum  = 1;
  private $path     = "";
  private $schemas  = array();
  private $exported = array();
  
  public function __construct($accessor, $connectionName)
  {
    $this->schemas = $accessor->getAll();
    $this->path = RUN_BASE . DS . "migration" . DS . $connectionName;
  }
  
  public function export()
  {
    if (empty($this->schemas)) return;
    
    foreach ($this->schemas as $tblName => $schema) {
      $fkey = $schema->getForeignKey();
      if ($fkey === null) {
        $this->doExport($schema);
        $this->exported[$tblName] = true;
        unset($this->schemas[$tblName]);
        continue;
      }
      
      $enable = true;
      foreach ($fkey->toArray() as $key) {
        $parent = $key->table;
        if ($parent === $tblName) continue;
        if (!isset($this->exported[$parent])) {
          $enable = false;
          break;
        }
      }
      
      if ($enable) {
        $this->doExport($schema);
        $this->exported[$tblName] = true;
        unset($this->schemas[$tblName]);
      }
    }
    
    $this->export();
  }
  
  public function doExport($tblSchema)
  {
    $tblName = $tblSchema->getTableName();
    if ($tblName === "sversion") return;
    
    $fileName = $this->fileNum . "_" . convert_to_modelname($tblName) . "_create.php";
    $filePath = $this->path . DS . $fileName;
    
    Sabel_Console::success("$fileName");
    
    $writer = new Sabel_Db_Migration_Writer($filePath);
    $writer->writeTable($tblSchema);
    
    // @todo
    $writer->write('$create->options("engine", "InnoDB");');
    $writer->close();
    
    $this->fileNum++;
  }
}
*/
