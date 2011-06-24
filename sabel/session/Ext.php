<?php

/**
 * Sabel_Session_Ext
 *
 * @abstract
 * @category   Session
 * @package    org.sabel.session
 * @author     Ebine Yutaka <ebine.yutaka@sabel.jp>
 * @copyright  2004-2008 Mori Reo <mori.reo@sabel.jp>
 * @license    http://www.opensource.org/licenses/bsd-license.php  BSD License
 */
abstract class Sabel_Session_Ext extends Sabel_Session_Abstract
{
  /**
   * @var int
   */
  protected $maxLifetime = 0;
  
  /**
   * @var boolean
   */
  protected $useOnlyCookies = false;
  
  /**
   * @var boolean
   */
  protected $useCookies = false;
  
  protected function readSessionSettings()
  {
    $maxLifetime = ini_get("session.gc_maxlifetime");
    $this->maxLifetime    = ($maxLifetime === "") ? 0 : (int)$maxLifetime;
    $this->useOnlyCookies = (ini_get("session.use_only_cookies") === "1");
    $this->useCookies     = (ini_get("session.use_cookies") === "1");
  }
  
  protected function initSession()
  {
    $sesName = session_name();
    
    if ($this->useOnlyCookies) {
      if (isset($_COOKIE[$sesName])) {
        return $_COOKIE[$sesName];
      } else {
        $sessionId = $this->createSessionId();
        $this->setSessionIdToCookie($sessionId);
        return $sessionId;
      }
    }
    
    if ($this->useCookies && isset($_COOKIE[$sesName])) {
      return $_COOKIE[$sesName];
    }
    
    $method = (isset($_SERVER["REQUEST_METHOD"])) ? $_SERVER["REQUEST_METHOD"] : "GET";
    
    if ($method !== "GET" && $method !== "POST") {
      return false;
    }
    
    $_VARS = ($method === "GET") ? $_GET : $_POST;
    $sessionId = (isset($_VARS[$sesName])) ? $_VARS[$sesName] : $this->createSessionId();
    
    if ($this->useCookies) {
      $this->setSessionIdToCookie($sessionId);
    }
    
    return $sessionId;
  }
  
  protected function setSessionIdToCookie($sessionId)
  {
    if ($this->useOnlyCookies || $this->useCookies) {
      setcookie(session_name(), $sessionId, 0, "/");
    }
  }
}
