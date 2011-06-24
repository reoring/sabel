<?php

/**
 * Sabel_Util_FileSystem
 *
 * @category   Util
 * @package    org.sabel.util
 * @author     Ebine Yutaka <ebine.yutaka@sabel.jp>
 * @copyright  2004-2008 Mori Reo <mori.reo@sabel.jp>
 * @license    http://www.opensource.org/licenses/bsd-license.php  BSD License
 */
class Sabel_Util_FileSystem extends Sabel_Util_FileSystem_Base
{
  public function __construct($base = "")
  {
    if ($base === "") {
      $this->path = (DS === '\\') ? "C:\\" : "/";
    } else {
      $this->path = realpath($base);
      
      if ($this->path === false) {
        trigger_error("no such file or directory.", E_USER_WARNING);
      }
    }
  }
  
  public function pwd()
  {
    return $this->path;
  }
  
  public function cd($path)
  {
    clearstatcache();
    
    if (!$this->isAbsolutePath($path)) {
      $path = $this->path . DS . $path;
    }
    
    if (is_dir($path)) {
      $this->path = realpath($path);
    } else {
      trigger_error("no such file or directory.", E_USER_WARNING);
    }
    
    return $this;
  }
  
  public function ls($path = null, $ignoreDotFiles = false)
  {
    if ($path === null) {
      $path = $this->path;
    } elseif (!$this->isAbsolutePath($path)) {
      $path = $this->path . DS . $path;
    }
    
    $items = array();
    foreach (scandir($path) as $item) {
      if ($item === "." || $item === "..") continue;
      if ($ignoreDotFiles && $item{0} === ".") continue;
      $items[] = $item;
    }
    
    return $items;
  }
  
  public function getList($path = null, $ignoreDotFiles = false)
  {
    if ($path === null) {
      $path = $this->path;
    } elseif (!$this->isAbsolutePath($path)) {
      $path = $this->path . DS . $path;
    }
    
    $items = array();
    foreach (scandir($path) as $item) {
      if ($item === "." || $item === "..") continue;
      if ($ignoreDotFiles && $item{0} === ".") continue;
      
      $path = $this->path . DS . $item;
      if (is_file($path)) {
        $items[] = new Sabel_Util_FileSystem_File($path);
      } else {
        $items[] = new self($path);
      }
    }
    
    return $items;
  }
  
  public function getDirectory($path)
  {
    if (!$this->isAbsolutePath($path)) {
      $path = $this->path . DS . $path;
    }
    
    if ($this->isDir($path)) {
      return new self($path);
    } else {
      $message = "'{$path}': no such file or directory.";
      throw new Sabel_Exception_Runtime($message);
    }
  }
  
  public function getFile($path)
  {
    if (!$this->isAbsolutePath($path)) {
      $path = $this->path . DS . $path;
    }
    
    if ($this->isFile($path)) {
      return new Sabel_Util_FileSystem_File($path);
    } else {
      $message = "'{$path}': no such file or directory.";
      throw new Sabel_Exception_Runtime($message);
    }
  }
  
  public function getDirectoryNames($ignoreDotFiles = false)
  {
    clearstatcache();
    
    $dirs = array();
    $path = $this->path;
    
    foreach (scandir($path) as $item) {
      if ($item === "." || $item === "..") continue;
      if ($ignoreDotFiles && $item{0} === ".") continue;
      if (is_dir($path . DS . $item)) $dirs[] = $item;
    }
    
    return $dirs;
  }
  
  public function getFileNames()
  {
    clearstatcache();
    
    $files = array();
    $path  = $this->path;
    
    foreach (scandir($path) as $item) {
      if (is_file($path . DS . $item)) $files[] = $item;
    }
    
    return $files;
  }
  
  public function mkdir($directory, $permission = 0755)
  {
    if (!$this->isAbsolutePath($directory)) {
      $directory = $this->path . DS . $directory;
    }
    
    if ($this->isDir($directory) || $this->isFile($directory)) {
      $message = "cannot create directory '{$directory}': "
               . "file or directory exists.";
      
      throw new Sabel_Exception_Runtime($message);
    } else {
      $this->_mkdir($directory, $permission);
      return new self($directory);
    }
  }
  
  public function mkfile($file, $permission = 0755)
  {
    if (!$this->isAbsolutePath($file)) {
      $file = $this->path . DS . $file;
    }
    
    if ($this->isDir($file) || $this->isFile($file)) {
      $message = "cannot create file '{$file}': "
               . "file or directory exists.";
      
      throw new Sabel_Exception_Runtime($message);
    } else {
      $this->_mkfile($file, $permission);
      return new Sabel_Util_FileSystem_File($file);
    }
  }
  
  public function rmdir($directory = null)
  {
    if ($directory === null) {
      $directory = $this->path;
    } elseif (!$this->isAbsolutePath($directory)) {
      $directory = $this->path . DS . $directory;
    }
    
    if (!$this->isDir($directory)) {
      trigger_error("no such file or directory.", E_USER_WARNING);
    } elseif ($this->isFile($directory)) {
      trigger_error("'{$directory}': not a directory.", E_USER_WARNING);
    } else {
      $this->_rmdir($directory);
      rmdir($directory);
    }
  }
  
  public function remove($path = null)
  {
    if ($path === null) {
      $path = $this->path;
    } elseif (!$this->isAbsolutePath($path)) {
      $path = $this->path . DS . $path;
    }
    
    if ($this->isDir($path)) {
      $this->rmdir($path);
    } else {
      unlink($path);
    }
  }
  
  public function copy($src, $dest)
  {
    if (!$this->isAbsolutePath($dest)) {
      $dest = $this->path . DS . $dest;
    }
    
    if (!$this->isAbsolutePath($src)) {
      $src = $this->path . DS . $src;
    }
    
    $dir = new self($src);
    $this->_mkdir($dest, $dir->getPermission());
    
    if ($items = $dir->getList()) {
      foreach ($items as $item) {
        $destination = $dest . DS . basename($item->getPath());
        
        if ($item->isFile()) {
          $item->copyTo($destination);
        } else {
          $this->copy($item->pwd(), $destination);
        }
      }
    }
  }
  
  public function copyTo($dest)
  {
    if (!$this->isAbsolutePath($dest)) {
      $dest = $this->path . DS . $dest;
    }
    
    $this->copy($this->path, $dest);
  }
  
  public function move($src, $dest)
  {
    if (!$this->isAbsolutePath($dest)) $dest = $this->path . DS . $dest;
    if (!$this->isAbsolutePath($src))  $src  = $this->path . DS . $src;
    
    $this->copy($src, $dest);
    $this->rmdir($src);
  }
  
  public function moveTo($dest)
  {
    if (!$this->isAbsolutePath($dest)) {
      $dest = $this->path . DS . $dest;
    }
    
    $this->move($this->path, $dest);
  }
  
  public function getSize($target = null)
  {
    if ($target === null) {
      $target = $this->path;
    } elseif (!$this->isAbsolutePath($target)) {
      $target = $this->path . DS . $target;
    }
    
    $size = 0;  // bytes
    foreach (scandir($target) as $item) {
      if ($item === "." || $item === "..") continue;
      $path = $target . DS . $item;
      if (is_link($path)) continue;
      
      if (is_file($path)) {
        $size += filesize($path);
      } else {
        $size += $this->getSize($path);
      }
    }
    
    return $size;
  }
  
  protected function _rmdir($directory)
  {
    clearstatcache();
    
    foreach (scandir($directory) as $item) {
      if ($item === "." || $item === "..") continue;
      $path = $directory . DS . $item;
      
      if (is_file($path)) {
        unlink($path);
      } elseif (is_dir($path)) {
        $this->_rmdir($path);
        rmdir($path);
      }
    }
  }
}
