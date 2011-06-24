<?php

/**
 * Processor_Helper
 *
 * @category   Addon
 * @package    addon.helper
 * @author     Mori Reo <mori.reo@sabel.jp>
 * @copyright  2004-2008 Mori Reo <mori.reo@sabel.jp>
 * @license    http://www.opensource.org/licenses/bsd-license.php  BSD License
 */
class Processor_Helper extends Sabel_Bus_Processor
{
  public function execute(Sabel_Bus $bus)
  {
    $destination    = $bus->get("destination");
    $moduleName     = $destination->getModule();
    $controllerName = $destination->getController();
    
    $sharedHelper  = "application";
    $commonHelpers = MODULES_DIR_PATH . DS . HELPERS_DIR_NAME;
    $moduleHelpers = MODULES_DIR_PATH . DS . $moduleName . DS . HELPERS_DIR_NAME;
    
    $helpers = array();
    
    $helpers[] = $commonHelpers . DS . $sharedHelper;
    $helpers[] = $moduleHelpers . DS . $sharedHelper;
    $helpers[] = $moduleHelpers . DS . $controllerName;
    
    foreach ($helpers as $helper) {
      Sabel::fileUsing($helper . ".php", true);
    }
  }
}
