<?php

##################### ENVIRONMENTS ########################

define("PRODUCTION",  0x01);
define("TEST",        0x02);
define("DEVELOPMENT", 0x04);

###################### SECURE MODE ########################

define("SBL_SECURE_MODE", true);

################### SABEL LOG LEVELS ######################

define("SBL_LOG_INFO",  0x01);
define("SBL_LOG_DEBUG", 0x02);
define("SBL_LOG_WARN",  0x04);
define("SBL_LOG_ERR",   0x08);
define("SBL_LOG_ALL",   0xFF);

############################################################

define("TPL_SUFFIX", ".tpl");
define("DS", DIRECTORY_SEPARATOR);

define("MODULES_DIR_NAME", "app");
define("VIEW_DIR_NAME",    "views");
define("HELPERS_DIR_NAME", "helpers");
define("LIB_DIR_NAME",     "lib");
define("ADDON_DIR_NAME",   "addon");

define("CONFIG_DIR_PATH",   RUN_BASE . DS  . "config");
define("MODULES_DIR_PATH",  RUN_BASE . DS  . MODULES_DIR_NAME);
define("MODELS_DIR_PATH",   MODULES_DIR_PATH . DS . "models");
define("LOG_DIR_PATH",      RUN_BASE . DS  . "logs");
define("CACHE_DIR_PATH",    RUN_BASE . DS  . "cache");
define("COMPILED_DIR_PATH", CACHE_DIR_PATH . DS . "templates");

define("APP_ENCODING", mb_internal_encoding());
define("DEFAULT_LAYOUT_NAME", "layout");

################# INCLUDE_PATH SETTINGS ####################

unshift_include_paths(array(
  MODULES_DIR_PATH,
  RUN_BASE . DS . LIB_DIR_NAME,
  MODELS_DIR_PATH,
  RUN_BASE . DS . ADDON_DIR_NAME
));

unshift_include_path(Sabel::getPath());

############### INCLUDE CONFIGURATION FILES ################

Sabel::fileUsing(CONFIG_DIR_PATH . DS . "Bus.php",       true);
Sabel::fileUsing(CONFIG_DIR_PATH . DS . "Map.php",       true);
Sabel::fileUsing(CONFIG_DIR_PATH . DS . "Addon.php",     true);
Sabel::fileUsing(CONFIG_DIR_PATH . DS . "Database.php",  true);
Sabel::fileUsing(CONFIG_DIR_PATH . DS . "Container.php", true);
