<?php

/**
 * cache to file
 *
 * @category   Cache
 * @package    org.sabel.cache
 * @author     Ebine Yutaka <ebine.yutaka@sabel.jp>
 * @copyright  2004-2008 Mori Reo <mori.reo@sabel.jp>
 * @license    http://www.opensource.org/licenses/bsd-license.php  BSD License
 */
class Sabel_Cache_File implements Sabel_Cache_Interface
{
  private static $instances = array();
  
  protected $dir = "";
  
  private function __construct($dir)
  {
    if (is_dir($dir)) {
      $this->dir = $dir;
    } else {
      $message = __METHOD__ . "() {$dir}";
      throw new Sabel_Exception_DirectoryNotFound($message);
    }
  }
  
  public static function create($dir = "")
  {
    if (empty($dir)) {
      if (defined("CACHE_DIR_PATH")) {
        $dir = CACHE_DIR_PATH;
      } else {
        $message = __METHOD__ . "() CACHE_DIR_PATH not defined.";
        throw new Sabel_Exception_Runtime($message);
      }
    }
    
    if (isset(self::$instances[$dir])) {
      return self::$instances[$dir];
    }
    
    return self::$instances[$dir] = new self($dir);
  }
  
  public function read($key)
  {
    $result = null;
    
    $path = $this->getPath($key);
    
    if (is_readable($path)) {
      $data = @unserialize(file_get_contents($path));
      
      if ($data !== false) {
        if ($data["timeout"] !== 0 && time() >= $data["timeout"]) {
          unlink($path);
        } else {
          $result = $data["value"];
        }
      }
    }
    
    return $result;
  }
  
  public function write($key, $value, $timeout = 0)
  {
    $data = array("value" => $value);
    
    if ($timeout !== 0) {
      $timeout = time() + $timeout;
    }
    
    $data["timeout"] = $timeout;
    file_put_contents($this->getPath($key), serialize($data), LOCK_EX);
  }
  
  public function delete($key)
  {
    $result = $this->read($key);
    
    $path = $this->getPath($key);
    if (is_file($path)) unlink($path);
    
    return $result;
  }
  
  protected function getPath($key)
  {
    return $this->dir . DS . $key . ".cache";
  }
}
