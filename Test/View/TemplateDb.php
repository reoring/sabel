<?php

/**
 * testcase for sabel.view.template.Database, sabel.view.Object
 * using database (mysql or pgsql, sqlite)
 *
 * @category  View
 * @author    Ebine Yutaka <ebine.yutaka@sabel.jp>
 */
class Test_View_TemplateDb extends Test_View_Template
{
  public static function suite()
  {
    $base = dirname(__FILE__) . DS . "templates";
    if (!defined("MODULES_DIR_PATH")) define("MODULES_DIR_PATH", $base);
    
    if (self::initTable()) {
      return self::createSuite("Test_View_TemplateDb");
    } else {
      return self::createSuite("");
    }
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
    $controller = new Sabel_View_Location_Database("index" . DS . VIEW_DIR_NAME . DS . $controllerName . DS);
    $view = new Sabel_View_Object("controller", $controller);
    
    $module = new Sabel_View_Location_Database("index" . DS . VIEW_DIR_NAME . DS);
    $view->addLocation("module", $module);
    
    $app = new Sabel_View_Location_Database(VIEW_DIR_NAME . DS);
    $view->addLocation("app", $app);
    
    return self::$view = $view;
  }
  
  private static function initTable()
  {
    if (extension_loaded("mysql")) {
      $params = array("package"  => "sabel.db.mysql",
                      "host"     => "127.0.0.1",
                      "user"     => "root",
                      "password" => "",
                      "database" => "sdb_test");
    } elseif (extension_loaded("pgsql")) {
      $params = array("package"  => "sabel.db.pgsql",
                      "host"     => "127.0.0.1",
                      "user"     => "root",
                      "password" => "",
                      "database" => "sdb_test");
    } elseif (extension_loaded("pdo_sqlite")) {
      $params = array("package"  => "sabel.db.pdo.sqlite",
                      "database" => SABEL_BASE . "/Test/data/sdb_test.sq3");
    } else {
      Sabel_Console::message("skipped 'TemplateDb'.");
      return false;
    }
    
    Sabel_Db_Config::add("default", $params);
    $stmt = Sabel_Db::createStatement();
    $tblName = $stmt->quoteIdentifier("sbl_template");
    $nCol    = $stmt->quoteIdentifier("name");
    $nsCol   = $stmt->quoteIdentifier("namespace");
    $cCol    = $stmt->quoteIdentifier("contents");
    $stmt->setQuery("DELETE FROM $tblName")->execute();
    
    $data = array();
    $data[0]["path"] = "views" . DS . "serverError" . TPL_SUFFIX;
    $data[0]["cont"] = "";
    $data[1]["path"] = "index" . DS . "views" . DS . "error" . TPL_SUFFIX;
    $data[1]["cont"] = "";
    $data[2]["path"] = "index" . DS . "views" . DS . "hoge" . DS . "index" . TPL_SUFFIX;
    $data[2]["cont"] = "hoge/index.tpl";
    $data[3]["path"] = "index" . DS . "views" . DS . "hoge" . DS . "hoge" . TPL_SUFFIX;
    $data[3]["cont"] = "hoge/hoge.tpl";
    $data[4]["path"] = "index" . DS . "views" . DS . "fuga" . DS . "index" . TPL_SUFFIX;
    $data[4]["cont"] = "fuga/index.tpl";
    $data[5]["path"] = "index" . DS . "views" . DS . "fuga" . DS . "fuga" . TPL_SUFFIX;
    $data[5]["cont"] = "fuga/fuga.tpl";
    
    foreach ($data as $d) {
      $query = "INSERT INTO {$tblName}({$nCol}, {$nsCol}, {$cCol}) VALUES('{$d["path"]}', '', '{$d["cont"]}')";
      $stmt->setQuery($query)->execute();
    }
    
    return true;
  }
}
