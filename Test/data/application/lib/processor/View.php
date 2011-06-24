<?php

class TestProcessor_View extends Sabel_Bus_Processor
{
  protected $beforeEvents = array("initializer" => "initViewObject");
  
  /**
   * @var Sabel_View
   */
  private $view = null;
  
  public function initViewObject($bus)
  {
    list ($m, $c, $a) = $bus->get("destination")->toArray();
    
    $view = new Sabel_View_Object("controller", new Sabel_View_Location_File(
      $m . DS . VIEW_DIR_NAME . DS . $c . DS)
    );
    
    $view->addLocation("module", new Sabel_View_Location_File($m . DS . VIEW_DIR_NAME . DS));
    $view->addLocation("app", new Sabel_View_Location_File(VIEW_DIR_NAME . DS));
    
    if ($renderer = $bus->get("renderer")) {
      $view->setRenderer($renderer);
    } else {
      $view->setRenderer(new Sabel_View_Renderer());
    }
    
    $this->view = $view;
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
    
    $view = $this->getView(
      $response->getStatus(),
      $bus->get("destination")->getAction(),
      $bus->get("IS_AJAX_REQUEST") === true
    );
    
    if ($contents === "") {
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
  
  protected function getView($status, $action, $isAjax = false)
  {
    if ($status->isFailure()) {
      $tplName = lcfirst(str_replace(" ", "", $status->getReason()));
      if ($location = $this->view->getValidLocation($tplName)) {
        $this->view->setName($tplName);
      } elseif ($status->isClientError()) {
        $this->view->setName("clientError");
      } else {
        $this->view->setName("serverError");
      }
    } elseif ($this->view->getName() === "") {
      $this->view->setName(($isAjax) ? "{$action}.ajax" : $action);
    }
    
    return $this->view;
  }
}
