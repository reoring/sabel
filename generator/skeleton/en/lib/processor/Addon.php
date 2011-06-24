<?php

/**
 * Processor_Addon
 *
 * @category   Processor
 * @package    lib.processor
 * @author     Mori Reo <mori.reo@sabel.jp>
 * @copyright  2004-2008 Mori Reo <mori.reo@sabel.jp>
 * @license    http://www.opensource.org/licenses/bsd-license.php  BSD License
 */
class Processor_Addon extends Sabel_Bus_Processor
{
  public function execute(Sabel_Bus $bus)
  {
    $config = $bus->getConfig("addon");
    $addons = $config->configure();
    
    foreach ($addons as $addon) {
      $className = ucfirst($addon) . "_Addon";
      $instance = new $className();
      $instance->execute($bus);
    }
  }
}
