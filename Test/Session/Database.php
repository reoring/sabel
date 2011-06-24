<?php

/**
 * testcase for sabel.session.Database
 *
 * @category  Session
 * @author    Ebine Yutaka <ebine.yutaka@sabel.jp>
 */
class Test_Session_Database extends SabelTestCase
{
  private static $sid = "";
  
  private $session = null;
  private $sessionId = "";
  
  public static function suite()
  {
    if (self::initTable()) {
      self::$sid = md5hash();
      ini_set("session.use_cookies", "0");
      return self::createSuite("Test_Session_Database");
    } else {
      return self::createSuite("");
    }
  }
  
  public function setUp()
  {
    $_SERVER["REQUEST_METHOD"] = "GET";
    $this->session = Sabel_Session_Database::create();
    $this->session->setTableName("session");
    $_GET[session_name()] = self::$sid;
    $this->session->start();
  }
  
  public function testEmpty()
  {
    $this->assertTrue($this->session->isStarted());
    $this->assertEquals(self::$sid, $this->session->getId());
    $this->assertNull($this->session->read("a"));
    $this->assertNull($this->session->read("b"));
    
    $this->session->write("a", "10");
    $this->session->shutdown();
  }
  
  public function testWrite()
  {
    $this->assertEquals("10", $this->session->read("a"));
    $this->assertNull($this->session->read("b"));
    
    $this->session->write("b", "20");
    $this->session->delete("a");
    $this->session->shutdown();
  }
  
  public function testDelete()
  {
    $this->assertNull($this->session->read("a"));
    $this->assertEquals("20", $this->session->read("b"));
    $this->session->shutdown();
  }
  
  public function testRegeneratedId()
  {
    $this->session->regenerateId();
    $newId = $this->session->getId();
    $this->assertNotEquals(self::$sid, $newId);
    
    $this->assertEquals("20", $this->session->read("b"));
    
    self::$sid = $newId;
    $this->session->shutdown();
  }
  
  public function testDestroy()
  {
    $this->assertEquals("20", $this->session->read("b"));
    $this->session->destroy();
    
    $this->assertNull($this->session->read("b"));
    $this->session->shutdown();
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
      Sabel_Console::message("skipped 'Test_Session_Database'.");
      return false;
    }
    
    Sabel_Db_Config::add("default", $params);
    Sabel_Db::createDriver("default")->execute("DELETE FROM session");
    return true;
  }
}
