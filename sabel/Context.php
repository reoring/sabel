<?php

/**
 * Sabel Context
 *
 * @category   Core
 * @package    org.sabel.core
 * @author     Mori Reo <mori.reo@sabel.jp>
 * @copyright  2004-2008 Mori Reo <mori.reo@sabel.jp>
 * @license    http://www.opensource.org/licenses/bsd-license.php  BSD License
 */
class Sabel_Context extends Sabel_Object
{
  protected static $context = null;
  
  protected $bus       = null;
  protected $candidate = null;
  protected $exception = null;
  
  public static function setContext($context)
  {
    self::$context = $context;
  }
  
  public static function getContext()
  {
    if (self::$context === null) {
      self::$context = new self();
    }
    
    return self::$context;
  }
  
  public function setBus($bus)
  {
    $this->bus = $bus;
  }
  
  public function getBus()
  {
    return $this->bus;
  }
  
  public function setCandidate($candidate)
  {
    $this->candidate = $candidate;
  }
  
  public function getCandidate()
  {
    return $this->candidate;
  }
  
  public function setException($exception)
  {
    $this->exception = $exception;
  }
  
  public function getException()
  {
    return $this->exception;
  }
  
  public static function getRequest()
  {
    $context = self::getContext();
    return ($context->bus) ? $context->bus->get("request") : null;
  }
  
  public static function getResponse()
  {
    $context = self::getContext();
    return ($context->bus) ? $context->bus->get("response") : null;
  }
  
  public static function getDestination()
  {
    $context = self::getContext();
    return ($context->bus) ? $context->bus->get("destination") : null;
  }
  
  public static function getSession()
  {
    $context = self::getContext();
    return ($context->bus) ? $context->bus->get("session") : null;
  }
  
  public static function getController()
  {
    $context = self::getContext();
    return ($context->bus) ? $context->bus->get("controller") : null;
  }
  
  public static function getView()
  {
    $context = self::getContext();
    return ($context->bus) ? $context->bus->get("view") : null;
  }
}
