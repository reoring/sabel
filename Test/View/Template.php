<?php

/**
 * @category  View
 * @author    Ebine Yutaka <ebine.yutaka@sabel.jp>
 */
abstract class Test_View_Template extends SabelTestCase
{
  protected static $view = null;
  
  public function testValidTemplate()
  {
    $template = self::$view->getValidLocation("index");
    $this->assertTrue($template instanceof Sabel_View_Location);
    $this->assertEquals(MODULES_DIR_PATH . DS . "index" . DS . VIEW_DIR_NAME . DS . "hoge" . DS . "index" . TPL_SUFFIX, $template->getPath());
    
    $template = self::$view->getValidLocation("hoge");
    $this->assertTrue($template instanceof Sabel_View_Location);
    $this->assertEquals(MODULES_DIR_PATH . DS . "index" . DS . VIEW_DIR_NAME . DS . "hoge" . DS . "hoge" . TPL_SUFFIX, $template->getPath());
    
    $template = self::$view->getValidLocation("error");
    $this->assertTrue($template instanceof Sabel_View_Location);
    $this->assertEquals(MODULES_DIR_PATH . DS . "index" . DS . VIEW_DIR_NAME . DS . "error" . TPL_SUFFIX, $template->getPath());
  }
  
  public function testInvalidTemplate()
  {
    $template = self::$view->getValidLocation("fuga");
    $this->assertNull($template);
    
    $template = self::$view->getValidLocation("abcdef");
    $this->assertNull($template);
  }
  
  public function testSetup2()
  {
    $repository = $this->createRepository("fuga");
    
    $this->assertEquals(3, count($repository->getLocations()));
    $this->assertTrue($repository->getLocation("controller") instanceof Sabel_View_Location);
    $this->assertTrue($repository->getLocation("module") instanceof Sabel_View_Location);
    $this->assertTrue($repository->getLocation("app") instanceof Sabel_View_Location);
    $this->assertNull($repository->getLocation("fuga"));
  }
  
  public function testValidTemplate2()
  {
    $template = self::$view->getValidLocation("index");
    $this->assertTrue($template instanceof Sabel_View_Location);
    $this->assertEquals(MODULES_DIR_PATH . DS . "index" . DS . VIEW_DIR_NAME . DS . "fuga" . DS . "index" . TPL_SUFFIX, $template->getPath());
    
    $template = self::$view->getValidLocation("fuga");
    $this->assertTrue($template instanceof Sabel_View_Location);
    $this->assertEquals(MODULES_DIR_PATH . DS . "index" . DS . VIEW_DIR_NAME . DS . "fuga" . DS . "fuga" . TPL_SUFFIX, $template->getPath());
    
    $template = self::$view->getValidLocation("error");
    $this->assertTrue($template instanceof Sabel_View_Location);
    $this->assertEquals(MODULES_DIR_PATH . DS . "index" . DS . VIEW_DIR_NAME . DS . "error" . TPL_SUFFIX, $template->getPath());
  }
  
  public function testInvalidTemplate2()
  {
    $template = self::$view->getValidLocation("hoge");
    $this->assertNull($template);
    
    $template = self::$view->getValidLocation("abcdef");
    $this->assertNull($template);
  }
  
  public function testGetContents()
  {
    $template = self::$view->getValidLocation("index");
    $contents = $template->getContents();
    $this->assertEquals("fuga/index.tpl", rtrim($contents));
    
    $template = self::$view->getValidLocation("fuga");
    $contents = $template->getContents();
    $this->assertEquals("fuga/fuga.tpl", rtrim($contents));
  }
  
  public function testIsValid()
  {
    $this->assertTrue(self::$view->isValid("controller", "index"));
    $this->assertTrue(self::$view->isValid("controller", "fuga"));
    
    $this->assertTrue(self::$view->isValid("module", "error"));
    $this->assertFalse(self::$view->isValid("module", "fuga"));
    
    $this->assertTrue(self::$view->isValid("app", "serverError"));
    $this->assertFalse(self::$view->isValid("app", "error"));
    $this->assertFalse(self::$view->isValid("app", "index"));
    
    try {
      self::$view->isValid("hoge", "index");
    } catch (Exception $e) {
      return;
    }
    
    $this->fail();
  }
  
  public function testCreate()
  {
    $time = microtime();
    self::$view->create("controller", "new", $time);
    $this->assertEquals($time, trim(self::$view->getContents("new")));
  }
  
  public function testDelete()
  {
    self::$view->delete("controller", "new");
    $this->assertNull(self::$view->getValidLocation("new"));
  }
}
