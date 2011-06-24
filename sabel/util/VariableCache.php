<?php

/**
 * Sabel_Util_VariableCache
 *
 * @category   Util
 * @package    org.sabel.util
 * @author     Ebine Yutaka <ebine.yutaka@sabel.jp>
 * @copyright  2004-2008 Mori Reo <mori.reo@sabel.jp>
 * @license    http://www.opensource.org/licenses/bsd-license.php  BSD License
 */
class Sabel_Util_VariableCache
{
  private static $instances = array();
  
  private $filePath = array();
  private $data = array();
  
  private function __construct()
  {
    
  }
  
  public static function create($_filePath)
  {
    if (isset(self::$instances[$_filePath])) {
      return self::$instances[$_filePath];
    }
    
    $_path = self::getPath($_filePath);
    $__var_cache = array();
    
    if (is_readable($_path)) {
      include ($_path);
    }
    
    $instance = new self();
    $instance->filePath = $_filePath;
    $instance->data = $__var_cache;
    
    self::$instances[$_filePath] = $instance;
    
    return $instance;
  }
  
  public function read($key)
  {
    if (isset($this->data[$key])) {
      return $this->data[$key];
    } else {
      return null;
    }
  }
  
  public function write($key, $value)
  {
    $this->data[$key] = $value;
  }
  
  public function delete($key)
  {
    unset($this->data[$key]);
  }
  
  public function save()
  {
    $contents = array();
    
    $r = preg_replace('/' . PHP_EOL . ' +\'/', "'", var_export($this->data, 1));
    $r = str_replace("' => '", "'=>'", $r);
    file_put_contents($this->getPath($this->filePath), '<?php $__var_cache = ' . $r . ';');
  }
  
  private static function getPath($key)
  {
    return CACHE_DIR_PATH . DS . $key . ".php";
  }
}
