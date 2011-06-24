<?php

/**
 * Sabel_Bus_Config
 *
 * @abstract
 * @category   Bus
 * @package    org.sabel.bus
 * @author     Ebine Yutaka <ebine.yutaka@sabel.jp>
 * @copyright  2004-2008 Mori Reo <mori.reo@sabel.jp>
 * @license    http://www.opensource.org/licenses/bsd-license.php  BSD License
 */
abstract class Sabel_Bus_Config extends Sabel_Object
{
  /**
   * @var Sabel_Bus_Processor[]
   */
  protected $processors = array();
  
  /**
   * @var Sabel_Config[]
   */
  protected $configs = array();
  
  /**
   * @var string[]
   */
  protected $interfaces = array();
  
  /**
   * @var boolean
   */
  protected $logging = false;
  
  /**
   * @return Sabel_Bus_Processor[]
   */
  public function getProcessors()
  {
    return $this->processors;
  }
  
  /**
   * @return Sabel_Config[]
   */
  public function getConfigs()
  {
    return $this->configs;
  }
  
  /**
   * @return string[]
   */
  public function getInterfaces()
  {
    return $this->interfaces;
  }
  
  /**
   * @return boolean
   */
  public function isLogging()
  {
    return $this->logging;
  }
}
