<?php

/**
 * interface of session objects
 *
 * @interface
 * @category   Session
 * @package    org.sabel.session
 * @author     Ebine Yutaka <ebine.yutaka@sabel.jp>
 * @copyright  2004-2008 Mori Reo <mori.reo@sabel.jp>
 * @license    http://www.opensource.org/licenses/bsd-license.php  BSD License
 */
interface Sabel_Session
{
  /**
   * @return void
   */
  public function start();
  
  /**
   * @return boolean
   */
  public function isStarted();
  
  /**
   * @return string
   */
  public function getName();
  
  /**
   * @param string
   *
   * @return void
   */
  public function setId($id);
  
  /**
   * @return string
   */
  public function getId();
  
  /**
   * @return void
   */
  public function regenerateId();
  
  /**
   * @return boolean
   */
  public function has($key);
  
  /**
   * @return mixed
   */
  public function read($key);
  
  /**
   * @param string $key
   * @param mixed  $value
   * @param int    $timeout
   *
   * @return void
   */
  public function write($key, $value, $timeout = 0);
  
  /**
   * @return mixed
   */
  public function delete($key);
  
  /**
   * @return array
   */
  public function destroy();
  
  /**
   * @return string
   */
  public function getClientId();
  
  /**
   * @return boolean
   */
  public function isCookieEnabled();
  
  /**
   * @return array
   */
  public function getTimeouts();
}
