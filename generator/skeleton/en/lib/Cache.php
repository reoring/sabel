<?php

class Cache
{
  private static $defaultBackend = "";
  
  public static function create($backend = "")
  {
    if (empty($backend) && empty(self::$defaultBackend)) {
      $message = __METHOD__ . "() must specify the backend.";
      throw new Sabel_Exception_Runtime($message);
    }
    
    if ((ENVIRONMENT & PRODUCTION) < 1) {
      return Sabel_Cache_Null::create();
    }
    
    if (empty($backend)) {
      $backend = self::$defaultBackend;
    }
    
    switch ($backend) {
      case "file":
        $storage = Sabel_Cache_File::create(CACHE_DIR_PATH . DS . "data");
        break;
      case "apc":
        $storage = Sabel_Cache_Apc::create();
        break;
      case "memcache":
        $storage = Sabel_Cache_Memcache::create(/* $host = "localhost", $port = 11211 */);
        // $storage->addServer(/* $host, $port = 11211, $weight = 1 */);
        break;
      default:
        $message = __METHOD__ . "() invalid cache backend.";
        throw new Sabel_Exception_Runtime($message);
    }
    
    return $storage;
  }
}
