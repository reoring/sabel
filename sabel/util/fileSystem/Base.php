<?php

/**
 * Sabel_Util_FileSystem_Base
 *
 * @category   Util
 * @package    org.sabel.util
 * @author     Ebine Yutaka <ebine.yutaka@sabel.jp>
 * @copyright  2004-2008 Mori Reo <mori.reo@sabel.jp>
 * @license    http://www.opensource.org/licenses/bsd-license.php  BSD License
 */
abstract class Sabel_Util_FileSystem_Base extends Sabel_Object
{
  protected $path = "";
  
  public function getPath()
  {
    return $this->path;
  }
  
  public function isDir($directory = null)
  {
    clearstatcache();
    
    if ($directory === null) {
      return is_dir($this->path);
    } elseif (!$this->isAbsolutePath($directory)) {
      $directory = $this->path . DIRECTORY_SEPARATOR . $directory;
    }
    
    return is_dir($directory);
  }
  
  public function isFile($file = null)
  {
    clearstatcache();
    
    if ($file === null) {
      return is_file($this->path);
    } elseif (!$this->isAbsolutePath($file)) {
      $file = $this->path . DIRECTORY_SEPARATOR . $file;
    }
    
    return is_file($file);
  }
  
  public function getPermission()
  {
    clearstatcache();
    return intval(substr(sprintf("%o", fileperms($this->path)), -4), 8);
  }
  
  public function chmod($permission)
  {
    chmod($this->path, $permission);
  }
  
  protected function isAbsolutePath($path)
  {
    static $isWin = null;
    
    if ($isWin === null) {
      $isWin = (DIRECTORY_SEPARATOR === '\\');
    }
    
    if ($isWin) {
      return (preg_match("@^[a-zA-Z]:\\\\@", $path) === 1);
    } else {
      return ($path{0} === "/");
    }
  }
  
  protected function _mkdir($directory, $permission)
  {
    clearstatcache();
    
    static $isWin = null;
    
    if ($isWin === null) {
      $isWin = (DIRECTORY_SEPARATOR === '\\');
    }
    
    $path  = "";
    $parts = explode(DIRECTORY_SEPARATOR, $directory);
    
    if ($isWin) {
      $path = $parts[0] . DIRECTORY_SEPARATOR . $parts[1];
      unset($parts[0]);
      unset($parts[1]);
    }
    
    foreach ($parts as $part) {
      if ($part === "") continue;
      $path .= DIRECTORY_SEPARATOR . $part;
      
      if (!is_dir($path)) {
        mkdir($path);
        chmod($path, $permission);
      }
    }
  }
  
  protected function _mkfile($filePath, $permission)
  {
    clearstatcache();
    
    $this->_mkdir(dirname($filePath), $permission);
    
    $fileName = basename($filePath);
    file_put_contents($filePath, "");
    chmod($filePath, $permission);
  }
}
