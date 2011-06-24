<?php

/**
 * Sabel_Cookie_Http
 *
 * @category   Cookie
 * @package    org.sabel.cookie
 * @author     Ebine Yutaka <ebine.yutaka@sabel.jp>
 * @copyright  2004-2008 Mori Reo <mori.reo@sabel.jp>
 * @license    http://www.opensource.org/licenses/bsd-license.php  BSD License
 */
class Sabel_Cookie_Http extends Sabel_Cookie_Abstract
{
  private static $instance = null;
  
  private function __construct()
  {
    
  }
  
  public static function create()
  {
    if (self::$instance === null) {
      self::$instance = new self();
    }
    
    return self::$instance;
  }
  
  public function set($key, $value, $options = array())
  {
    $options = $this->createOptions($options);
    
    setcookie($key,
              $value,
              $options["expire"],
              $options["path"],
              $options["domain"],
              $options["secure"],
              $options["httpOnly"]);
  }
  
  public function rawset($key, $value, $options = array())
  {
    $options = $this->createOptions($options);
    
    setrawcookie($key,
                 $value,
                 $options["expire"],
                 $options["path"],
                 $options["domain"],
                 $options["secure"],
                 $options["httpOnly"]);
  }
  
  public function get($key)
  {
    if (array_key_exists($key, $_COOKIE)) {
      return $_COOKIE[$key];
    } else {
      return null;
    }
  }
}
