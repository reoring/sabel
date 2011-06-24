<?php

class TestProcessor_Session extends Sabel_Bus_Processor
{
  public function execute(Sabel_Bus $bus)
  {
    if (!$bus->has("session")) {
      $bus->set("session", Sabel_Session_PHP::create());
    }
  }
  
  public function shutdown(Sabel_Bus $bus)
  {
    $session = $bus->get("session");
    
    if ($session->isStarted() && !$session->isCookieEnabled() && ini_get("session.use_trans_sid") === "0") {
      output_add_rewrite_var($session->getName(), $session->getId());
    }
  }
}
