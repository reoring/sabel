<?php

/**
 * Processor_Action
 *
 * @category   Processor
 * @package    lib.processor
 * @author     Mori Reo <mori.reo@sabel.jp>
 * @author     Ebine Yutaka <ebine.yutaka@sabel.jp>
 * @copyright  2004-2008 Mori Reo <mori.reo@sabel.jp>
 * @license    http://www.opensource.org/licenses/bsd-license.php  BSD License
 */
class Processor_Action extends Sabel_Bus_Processor
{
  public function execute(Sabel_Bus $bus)
  {
    $response   = $bus->get("response");
    $controller = $bus->get("controller");
    
    if ($response->isFailure() || $response->isRedirected()) return;
    
    $action = $bus->get("destination")->getAction();
    $controller->setAction($action);
    
    try {
      $controller->initialize();
      
      if ($response->isSuccess() && !$response->isRedirected()) {
        $controller->execute();
      }
      
      $controller->finalize();
    } catch (Exception $e) {
      $response->getStatus()->setCode(Sabel_Response::INTERNAL_SERVER_ERROR);
      Sabel_Context::getContext()->setException($e);
    }
    
    if ($controller->getAttribute("layout") === false) {
      $bus->set("NO_LAYOUT", true);
    }
  }
}
