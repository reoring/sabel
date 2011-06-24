<?php

/**
 * Processor_Initializer
 *
 * @category   Processor
 * @package    lib.processor
 * @author     Ebine Yutaka <ebine.yutaka@sabel.jp>
 * @copyright  2004-2008 Mori Reo <mori.reo@sabel.jp>
 * @license    http://www.opensource.org/licenses/bsd-license.php  BSD License
 */
class Processor_Initializer extends Sabel_Bus_Processor
{
  public function execute(Sabel_Bus $bus)
  {
    Sabel_Db_Config::initialize($bus->getConfig("database"));
    
    if (!is_cli() && ($session = $bus->get("session")) !== null) {
      $session->start();
      l("START SESSION: " . $session->getName() . "=" . $session->getId());
    }
    
    // default page title.
    if ($response = $bus->get("response")) {
      $response->setResponse("pageTitle", "Sabel");
    }
  }
}
