<?php

class Test_Aspect_Config extends SabelTestCase
{
  public static function suite()
  {
    return self::createSuite("Test_Aspect_Config");
  }
  
  public function testConfig()
  {
    $config = new Sabel_Test_Aspect_Config();
    $config->configure();
  }
}

class Sabel_Test_Aspect_Config extends Sabel_Aspect_Config implements Sabel_Config
{
  public function configure()
  {
    $this->aspect("default");
  }
}