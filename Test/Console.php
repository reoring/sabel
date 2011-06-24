<?php

/**
 * testcase of sabel.Console
 *
 * @category  Sakle
 * @author    Ebine Yutaka <ebine.yutaka@sabel.jp>
 */
class Test_Console extends SabelTestCase
{
  public static function suite()
  {
    return self::createSuite("Test_Console");
  }
  
  public function testMessage()
  {
    $_SERVER["IS_WINDOWS"] = true;
    
    ob_start();
    Sabel_Console::success("success");
    $result = ob_get_clean();
    $this->assertEquals("[SUCCESS] success", rtrim($result));
    
    ob_start();
    Sabel_Console::warning("warning");
    $result = ob_get_clean();
    $this->assertEquals("[WARNING] warning", rtrim($result));
    
    ob_start();
    Sabel_Console::error("failure");
    $result = ob_get_clean();
    $this->assertEquals("[FAILURE] failure", rtrim($result));
    
    ob_start();
    Sabel_Console::message("message");
    $result = ob_get_clean();
    $this->assertEquals("[MESSAGE] message", rtrim($result));
  }
  
  public function testHasOption()
  {
    $args = array("rm", "-r");
    $this->assertTrue(Sabel_Console::hasOption("r", $args));
     
    $args = array("rm", "-r", "-f");
    $this->assertTrue(Sabel_Console::hasOption("r", $args));
    $this->assertTrue(Sabel_Console::hasOption("f", $args));
     
    $args = array("rm", "-rf");
    $this->assertTrue(Sabel_Console::hasOption("r", $args));
    $this->assertTrue(Sabel_Console::hasOption("f", $args));
  }
  
  public function testHasOption2()
  {
    $args = array("cmd", "--foo");
    $this->assertTrue(Sabel_Console::hasOption("foo", $args));
    
    $args = array("cmd", "--foo=bar");
    $this->assertTrue(Sabel_Console::hasOption("foo", $args));
  }
  
  public function testGetOption()
  {
    $args = array("cmd", "-d", "/var/tmp", "-f", "/tmp/test.txt");
    $this->assertTrue(Sabel_Console::hasOption("d", $args));
    $this->assertTrue(Sabel_Console::hasOption("f", $args));
    
    $args = array("cmd", "-d", "/var/tmp", "-f", "/tmp/test.txt");
    $this->assertEquals("/var/tmp", Sabel_Console::getOption("d", $args));
    $this->assertEquals(array("cmd", "-f", "/tmp/test.txt"), $args);
    
    $args = array("cmd", "-d", "/var/tmp", "-f", "/tmp/test.txt");
    $this->assertEquals("/tmp/test.txt", Sabel_Console::getOption("f", $args));
    $this->assertEquals(array("cmd", "-d", "/var/tmp"), $args);
  }
  
  public function testGetOption2()
  {
    $args = array("cmd", "--dir=/var/tmp", "--file=/tmp/test.txt");
    $this->assertEquals("/var/tmp", Sabel_Console::getOption("dir", $args));
    $this->assertEquals(array("cmd", "--file=/tmp/test.txt"), $args);
    
    $args = array("cmd", "--dir=/var/tmp", "--file=/tmp/test.txt");
    $this->assertEquals("/tmp/test.txt", Sabel_Console::getOption("file", $args));
    $this->assertEquals(array("cmd", "--dir=/var/tmp"), $args);
  }
}
