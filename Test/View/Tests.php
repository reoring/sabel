<?php

require_once ("Test/View/Template.php");
require_once ("Test/View/TemplateFile.php");
require_once ("Test/View/TemplateDb.php");
require_once ("Test/View/Renderer.php");
require_once ("Test/View/Pager.php");
require_once ("Test/View/PageViewer.php");

/**
 * load tests for sabel.view
 *
 * @category  View
 * @author    Ebine Yutaka <ebine.yutaka@sabel.jp>
 */
class Test_View_Tests
{
  public static function suite()
  {
    $suite = new PHPUnit_Framework_TestSuite();
    $suite->addTest(Test_View_TemplateFile::suite());
    // $suite->addTest(Test_View_TemplateDb::suite());
    $suite->addTest(Test_View_Renderer::suite());
    $suite->addTest(Test_View_Pager::suite());
    $suite->addTest(Test_View_PageViewer::suite());
    
    return $suite;
  }
}
