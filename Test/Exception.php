<?php

/**
 * test case for sabel.exception.*
 *
 * @category  Exception
 * @author    Ebine Yutaka <ebine.yutaka@sabel.jp>
 */
class Test_Exception extends SabelTestCase
{
  public static function suite()
  {
    return self::createSuite("Test_Exception");
  }
  
  public function testInvalidArgument()
  {
    try {
      $this->throwInvalidArgumentException();
    } catch (Sabel_Exception_InvalidArgument $e) {
      $this->assertTrue($e instanceof InvalidArgumentException); // SPL
      $this->assertEquals("invalid argument.", $e->getMessage());
      return;
    }
    
    $this->fail();
  }
  
  public function testRuntime()
  {
    try {
      $this->throwRuntimeException();
    } catch (Sabel_Exception_Runtime $e) {
    $message = <<<MESSAGE
throw
runtime
exception
MESSAGE;
      
      $messageLines = $e->writeSyslog($message);
      $this->assertEquals(3, count($messageLines));
      $this->assertEquals("throw",     $messageLines[0]);
      $this->assertEquals("runtime",   $messageLines[1]);
      $this->assertEquals("exception", $messageLines[2]);
      return;
    }
    
    $this->fail();
  }
  
  protected function throwInvalidArgumentException()
  {
    throw new Sabel_Exception_InvalidArgument("invalid argument.");
  }
  
  protected function throwRuntimeException()
  {
    throw new Sabel_Exception_Runtime("");
  }
}
