<?php

/**
 * Sabel_Logger_File
 *
 * @category   Logger
 * @package    org.sabel.logger
 * @author     Ebine Yutaka <ebine.yutaka@sabel.jp>
 * @copyright  2004-2008 Mori Reo <mori.reo@sabel.jp>
 * @license    http://www.opensource.org/licenses/bsd-license.php  BSD License
 */
class Sabel_Logger_File extends Sabel_Object implements Sabel_Logger_Interface
{
  const DEFAULT_LOG_FILE = "sabel.log";
  
  public function output($allMessages)
  {
    if (empty($allMessages)) return;
    
    foreach ($allMessages as $identifier => $messages) {
      $fp  = $this->open($identifier);
      $sep = "============================================================" . PHP_EOL;
      fwrite($fp, PHP_EOL . $sep . PHP_EOL);
      
      $msgs = array();
      foreach ($messages as $message) {
        $msgs[] = $message["time"]
                . " [" . $this->defineToString($message["level"])
                . "] " . $message["message"];
      }
      
      fwrite($fp, implode(PHP_EOL, $msgs) . PHP_EOL);
      fclose($fp);
    }
  }
  
  public function write($identifier, $message)
  {
    $msg = $message["time"]
         . " [" . $this->defineToString($message["level"])
         . "] " . $message["message"];
    
    $fp = $this->open($identifier);
    fwrite($fp, $msg . PHP_EOL);
    fclose($fp);
  }
  
  protected function defineToString($level)
  {
    switch ($level) {
      case SBL_LOG_INFO:
        return "info";
      case SBL_LOG_DEBUG:
        return "debug";
      case SBL_LOG_WARN:
        return "warning";
      case SBL_LOG_ERR:
        return "error";
    }
  }
  
  protected function open($identifier)
  {
    $filePath = "";
    
    if ($identifier === "default") {
      if (!defined("ENVIRONMENT")) {
        $name = "test";
      } else {
        switch (ENVIRONMENT) {
          case PRODUCTION:
            $name = "production";
            break;
          case DEVELOPMENT:
            $name = "development";
            break;
          default:
            $name = "test";
        }
      }
      
      $filePath = LOG_DIR_PATH . DS . $name . "." . self::DEFAULT_LOG_FILE;
    } else {
      $filePath = LOG_DIR_PATH . DS . $identifier . ".log";
    }
    
    if (!is_file($filePath)) {
      touch($filePath);
      chmod($filePath, 0777);
    }
    
    return fopen($filePath, "a");
  }
}
