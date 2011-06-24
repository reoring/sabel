<?php

/**
 * testcase for sabel.view.template.File, sabel.view.Object
 *
 * @category  View
 * @author    Ebine Yutaka <ebine.yutaka@sabel.jp>
 */
class Test_View_TemplateFile extends Test_View_Template
{
  public static function suite()
  {
    return self::createSuite("Test_View_TemplateFile");
  }
  
  public function testSetup()
  {
    $repository = $this->createRepository("hoge");
    
    $this->assertEquals(3, count($repository->getLocations()));
    $this->assertTrue($repository->getLocation("controller") instanceof Sabel_View_Location);
    $this->assertTrue($repository->getLocation("module") instanceof Sabel_View_Location);
    $this->assertTrue($repository->getLocation("app") instanceof Sabel_View_Location);
    $this->assertNull($repository->getLocation("hoge"));
  }
  
  protected function createRepository($controllerName)
  {
    $controller = new Sabel_View_Location_File("index" . DS . VIEW_DIR_NAME . DS . $controllerName . DS);
    $view = new Sabel_View_Object("controller", $controller);
    
    $module = new Sabel_View_Location_File("index" . DS . VIEW_DIR_NAME . DS);
    $view->addLocation("module", $module);
    
    $app = new Sabel_View_Location_File(VIEW_DIR_NAME . DS);
    $view->addLocation("app", $app);
    
    return self::$view = $view;
  }
}
