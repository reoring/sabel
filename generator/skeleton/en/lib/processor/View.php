<?php

/**
 * Processor_View
 *
 * @category   Processor
 * @package    lib.processor
 * @author     Ebine Yutaka <ebine.yutaka@sabel.jp>
 * @copyright  2004-2008 Mori Reo <mori.reo@sabel.jp>
 * @license    http://www.opensource.org/licenses/bsd-license.php  BSD License
 */
class Processor_View extends Sabel_Bus_Processor
{
  protected $afterEvents = array("controller" => "initViewObject");
  
  public function initViewObject($bus)
  {
    list ($m, $c, $a) = $bus->get("destination")->toArray();
    
    $view = new Sabel_View_Object("controller", new Sabel_View_Location_File(
      $m . DS . VIEW_DIR_NAME . DS . $c . DS
    ));
    
    $view->addLocation("module", new Sabel_View_Location_File($m . DS . VIEW_DIR_NAME . DS));
    $view->addLocation("app", new Sabel_View_Location_File(VIEW_DIR_NAME . DS));
    
    if ($renderer = $bus->get("renderer")) {
      $view->setRenderer($renderer);
    } else {
      $view->setRenderer(new Sabel_View_Renderer());
    }
    
    $bus->set("view", $view);
    $bus->get("controller")->setAttribute("view", $view);
  }
  
  public function execute(Sabel_Bus $bus)
  {
    $response = $bus->get("response");
    if ($response->isRedirected()) return;
    
    $controller = $bus->get("controller");
    $responses  = $response->getResponses();
    $contents   = (isset($responses["contents"])) ? $responses["contents"] : "";
    
    $view = $this->setTemplateName(
      $bus->get("view"),
      $response->getStatus(),
      $bus->get("destination")->getAction(),
      $bus->get("IS_AJAX_REQUEST") === true
    );
    
    if (is_empty($contents)) {
      if ($location = $view->getValidLocation()) {
        $contents = $view->rendering($location, $responses);
      } elseif (!$controller->isExecuted()) {
        $response->getStatus()->setCode(Sabel_Response::NOT_FOUND);
        if ($location = $view->getValidLocation("notFound")) {
          $contents = $view->rendering($location, $responses);
        } else {
          $contents = "<h1>404 Not Found</h1>";
        }
      }
    }
    
    if ($bus->get("NO_LAYOUT")) {
      $bus->set("result", $contents);
    } else {
      $layout = (isset($responses["layout"])) ? $responses["layout"] : DEFAULT_LAYOUT_NAME;
      if ($location = $view->getValidLocation($layout)) {
        $responses["contentForLayout"] = $contents;
        $bus->set("result", $view->rendering($location, $responses));
      } else {  // no layout.
        $bus->set("result", $contents);
      }
    }
  }
  
  protected function setTemplateName(Sabel_View $view, $status, $action, $isAjax = false)
  {
    if ($status->isFailure()) {
      $tplName = lcfirst(str_replace(" ", "", $status->getReason()));
      if ($location = $view->getValidLocation($tplName)) {
        $view->setName($tplName);
      } elseif ($status->isClientError()) {
        $view->setName("clientError");
      } else {
        $view->setName("serverError");
      }
    } elseif ($view->getName() === "") {
      $view->setName(($isAjax) ? "{$action}.ajax" : $action);
    }
    
    return $view;
  }
}
