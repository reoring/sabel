<?php

/**
 * define sabel environment.
 */
// if (!defined("ENVIRONMENT")) define("ENVIRONMENT", PRODUCTION);
// if (!defined("ENVIRONMENT")) define("ENVIRONMENT", TEST);
if (!defined("ENVIRONMENT")) define("ENVIRONMENT", DEVELOPMENT);

if ((ENVIRONMENT & PRODUCTION) > 0) {
  ini_set("display_errors", "0");
  ini_set("log_errors", "1");
  error_reporting(E_ALL);
  define("SBL_LOG_LEVEL", SBL_LOG_ERR);
} else {
  ini_set("display_errors", "1");
  error_reporting(E_ALL|E_STRICT);
  define("SBL_LOG_LEVEL", SBL_LOG_ALL);
}

//define("SERVICE_DOMAIN", "www.example.com");
//define("FILEINFO_MAGICDB", "/usr/share/misc/magic");
