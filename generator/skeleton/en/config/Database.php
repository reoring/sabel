<?php

class Config_Database implements Sabel_Config
{
  public function configure()
  {
    switch (ENVIRONMENT) {
      case PRODUCTION:
        $params = array("default" => array(
                          "package"  => "sabel.db.*",
                          "host"     => "localhost",
                          "database" => "dbname",
                          "user"     => "user",
                          "password" => "password")
                       );
        break;
        
      case TEST:
        $params = array("default" => array(
                          "package"  => "sabel.db.*",
                          "host"     => "localhost",
                          "database" => "dbname",
                          "user"     => "user",
                          "password" => "password")
                       );
        break;
        
      case DEVELOPMENT:
        $params = array("default" => array(
                          "package"  => "sabel.db.*",
                          "host"     => "localhost",
                          "database" => "dbname",
                          "user"     => "user",
                          "password" => "password")
                       );
        break;
    }
    
    return $params;
  }
}
