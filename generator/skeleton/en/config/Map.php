<?php

class Config_Map extends Sabel_Map_Configurator
{
  public function configure()
  {
    $this->route("default")
           ->uri(":controller/:action")
           ->module("index")
           ->defaults(array(
             ":controller" => "index",
             ":action"     => "index")
           );
    
    $this->route("notfound")
           ->uri("*")
           ->module("index")
           ->controller("index")
           ->action("notFound");
  }
}
