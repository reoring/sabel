<?php

class TestProcessor_Initializer extends Sabel_Bus_Processor
{
  public function execute(Sabel_Bus $bus)
  {
    Sabel_Db_Config::initialize($bus->getConfig("database"));
    //Sabel::fileUsing(RUN_BASE . DS . LIB_DIR_NAME . DS . "db" . DS . "utility.php", true);
    
    if (!defined("SBL_BATCH")) {
      // start session.
      $session = $bus->get("session");
      $session->start();
      l("START SESSION: " . $session->getName() . "=" . $session->getId());
    }
    
    // default page title.
    $bus->get("response")->setResponse("pageTitle", "Sabel");
    
    // $request = $bus->get("request");
    // if ($request->isPost()) $this->trim($request);
  }

  /**
   * strip whitespace from post values.
   */
  private function trim($request)
  {
    $func = (extension_loaded("mbstring")) ? "mb_trim" : "trim";
    
    if ($values = $request->fetchPostValues()) {
      foreach ($values as &$value) {
        if ($value === null || is_array($value)) continue;
        $result = $func($value);
        $value  = ($result === "") ? null : $result;
      }
      
      $request->setPostValues($values);
    }
  }
}
