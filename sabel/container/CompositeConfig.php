<?php

class Sabel_Container_CompositeConfig extends Sabel_Container_Injection
{
  private $configs;
  
  public function __construct()
  {
    $this->configs = array();
  }
  
  public function add(Sabel_Container_Injection $config)
  {
    $config->configure();
    $this->configs[] = $config;
  }
  
  public function configure()
  {
    foreach ($this->configs as $config) {
      $this->binds      = array_merge($this->binds,      $config->binds);
      $this->aspects    = array_merge($this->aspects,    $config->aspects);
      $this->lifecycle  = array_merge($this->lifecycle,  $config->lifecycle);
      $this->constructs = array_merge($this->constructs, $config->constructs);
    }
  }
}