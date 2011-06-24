<?php

class Config_Container extends Sabel_Container_Injection
{
  public function configure()
  {
    
  }
}

Sabel_Container::setDefaultConfig(new Config_Container());
