<?php

class TestProcessor_Helper extends Sabel_Bus_Processor
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
