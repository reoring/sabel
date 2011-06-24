<?php

/**
 * Processor_Session
 *
 * @category   Processor
 * @package    lib.processor
 * @author     Ebine Yutaka <ebine.yutaka@sabel.jp>
 * @copyright  2004-2008 Mori Reo <mori.reo@sabel.jp>
 * @license    http://www.opensource.org/licenses/bsd-license.php  BSD License
 */
class Processor_Session extends Sabel_Bus_Processor
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
    
    if ($session->isStarted()) {
      if (!$session->isCookieEnabled() && ini_get("session.use_trans_sid") === "0") {
        output_add_rewrite_var($session->getName(), $session->getId());
      }
    }
  }
}
