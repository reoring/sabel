<?php

/**
 * Processor_Request
 *
 * @category   Processor
 * @package    lib.processor
 * @author     Mori Reo <mori.reo@sabel.jp>
 * @copyright  2004-2008 Mori Reo <mori.reo@sabel.jp>
 * @license    http://www.opensource.org/licenses/bsd-license.php  BSD License
 */
class Processor_Request extends Sabel_Bus_Processor
{
  public function execute(Sabel_Bus $bus)
  {
    if ($bus->has("request")) {
      $request = $bus->get("request");
    } else {
      $uri = $this->getRequestUri($bus);
      $request = new Sabel_Request_Object($uri);
      
      if (SBL_SECURE_MODE) {
        $_GET  = remove_nullbyte($_GET);
        $_POST = remove_nullbyte($_POST);
      }
      
      $request->setGetValues($_GET);
      $request->setPostValues($_POST);
      
      $this->setFiles($request);
      
      if (isset($_SERVER["REQUEST_METHOD"])) {
        $request->method($_SERVER["REQUEST_METHOD"]);
      }
      
      $httpHeaders = array();
      foreach ($_SERVER as $key => $val) {
        if (strpos($key, "HTTP") === 0) {
          $httpHeaders[$key] = $val;
        }
      }
      
      $request->setHttpHeaders($httpHeaders);
      $bus->set("request", $request);
    }
    
    l("REQUEST URI: /" . $request->getUri(true));
    
    // Ajax request.
    if ($request->getHttpHeader("X-Requested-With") === "XMLHttpRequest") {
      $bus->set("NO_LAYOUT", true);
      $bus->set("IS_AJAX_REQUEST", true);
    }
  }
  
  protected function getRequestUri($bus)
  {
    $uri = (isset($_SERVER["REQUEST_URI"])) ? $_SERVER["REQUEST_URI"] : "/";
    
    if (!is_cli() && isset($_SERVER["SCRIPT_NAME"]) && $_SERVER["SCRIPT_NAME"] !== "/index.php") {
      $bus->set("NO_VIRTUAL_HOST", true);
      
      $pubdir = substr(RUN_BASE . DS . "public", strlen($_SERVER["DOCUMENT_ROOT"]));
      define("URI_PREFIX", $pubdir);
      
      $uri = substr(str_replace("/index.php", "", $uri), strlen($pubdir));
    }
    
    return normalize_uri($uri);
  }
  
  protected function setFiles(Sabel_Request $request)
  {
    if (!empty($_FILES)) {
      foreach ($_FILES as $name => $_FILE) {
        if (isset($_FILE["tmp_name"]) && isset($_FILE["size"]) && $_FILE["size"] > 0) {
          $file = new Sabel_Request_File();
          $file->name = (isset($_FILE["name"])) ? $_FILE["name"] : ""; 
          $file->type = (isset($_FILE["type"])) ? $_FILE["type"] : ""; 
          $file->path = $_FILE["tmp_name"];
          $file->size = $_FILE["size"];
          
          $request->setFile($name, $file);
        }
      }
    }
  }
}
