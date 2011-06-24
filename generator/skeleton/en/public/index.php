<?php

ob_start();

define("RUN_BASE", dirname(realpath(".")));

require ("Sabel"  . DIRECTORY_SEPARATOR . "Sabel.php");
require (RUN_BASE . DIRECTORY_SEPARATOR . "config" . DIRECTORY_SEPARATOR . "INIT.php");
require (RUN_BASE . DIRECTORY_SEPARATOR . "config" . DIRECTORY_SEPARATOR . "environment.php");

if (!defined("ENVIRONMENT")) {
  echo "SABEL FATAL ERROR: must define ENVIRONMENT in config/environment.php";
  exit;
}

if ((ENVIRONMENT & PRODUCTION) > 0) {
  Sabel::init();
  echo Sabel_Bus::create()->run(new Config_Bus());
  Sabel::shutdown();
} else {
  echo Sabel_Bus::create()->run(new Config_Bus());
}

ob_flush();
