<?php

class TestProcessor_Request extends Sabel_Bus_Processor
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
    
    // ajax request.
    if ($request->getHttpHeader("X-Requested-With") === "XMLHttpRequest") {
      $bus->set("NO_LAYOUT", true);
      $bus->set("IS_AJAX_REQUEST", true);
    }
  }
  
  protected function getRequestUri($bus)
  {
    $uri = (isset($_SERVER["REQUEST_URI"])) ? $_SERVER["REQUEST_URI"] : "/";
    
    if (isset($_SERVER["SCRIPT_NAME"]) && strpos($_SERVER["SCRIPT_NAME"], "/index.php") >= 1) {
      $uri = substr($uri, strlen($_SERVER["SCRIPT_NAME"]));
      $bus->set("NO_VIRTUAL_HOST", true);
    }
    
    if (defined("NO_REWRITE_PREFIX") && isset($_GET[NO_REWRITE_PREFIX])) {
      $uri = substr($uri, strlen(NO_REWRITE_PREFIX) + 2);
      $parsed = parse_url($uri);
      if (isset($parsed["query"])) {
        parse_str($parsed["query"], $_GET);
      }
      
      unset($_GET[NO_REWRITE_PREFIX]);
      $bus->set("NO_REWRITE_MODULE", true);
    }
    
    return normalize_uri($uri);
  }
}
