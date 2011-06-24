<?php

class TestProcessor_Addon extends Sabel_Bus_Processor
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
