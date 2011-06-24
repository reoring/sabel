<?php

/**
 * testcase of sabel.response.Object
 *
 * @category Response
 * @author   Ebine Yutaka <ebine.yutaka@sabel.jp>
 */
class Test_Response_Object extends SabelTestCase
{
  public static function suite()
  {
    return self::createSuite("Test_Response_Object");
  }
  
  public function testResponseValue()
  {
    $response = new Sabel_Response_Object();
    $response->setResponse("a", "10");
    $response->setResponse("b", "20");
    
    $this->assertEquals("10", $response->getResponse("a"));
    $this->assertEquals("20", $response->getResponse("b"));
    $this->assertEquals(null, $response->getResponse("c"));
  }
  
  public function testResponseValues()
  {
    $response = new Sabel_Response_Object();
    $response->setResponses(array("a" => "10", "b" => "20"));
    
    $this->assertEquals("10", $response->getResponse("a"));
    $this->assertEquals("20", $response->getResponse("b"));
    $this->assertEquals(null, $response->getResponse("c"));
    
    $expected = array("a" => "10", "b" => "20");
    $this->assertEquals($expected, $response->getResponses());
  }
  
  public function testResponseHeader()
  {
    $response = new Sabel_Response_Object();
    $response->setHeader("Content-Type",   "image/gif");
    $response->setHeader("Content-Length", "4096");
    $this->assertEquals("image/gif", $response->getHeader("Content-Type"));
    $this->assertEquals("4096", $response->getHeader("Content-Length"));
    $this->assertEquals(array("Content-Type" => "image/gif", "Content-Length" => "4096"), $response->getHeaders());
    $this->assertEquals(null, $response->getHeader("Foo-Bar"));
  }
  
  public function testExpiredCacheHeaders()
  {
    $response = new Sabel_Response_Object();
    $response->expiredCache("300000000");
    $headers = $response->getHeaders();
    $this->assertTrue(isset($headers["Expires"]));
    $this->assertTrue(isset($headers["Last-Modified"]));
    $this->assertTrue(isset($headers["Cache-Control"]));
    $this->assertTrue(isset($headers["Pragma"]));
  }
  
  public function testStatus()
  {
    $response = new Sabel_Response_Object();
    $status = $response->getStatus();
    
    $this->assertTrue($status->isSuccess());
    
    $status->setCode(Sabel_Response::NOT_FOUND);
    $this->assertTrue($status->isClientError());
    
    $status->setCode(Sabel_Response::INTERNAL_SERVER_ERROR);
    $this->assertTrue($status->isServerError());
  }
  
  public function testIsFailure()
  {
    $response = new Sabel_Response_Object();
    $status = $response->getStatus();
    
    $this->assertFalse($status->isFailure());
    
    $status->setCode(Sabel_Response::NOT_FOUND);
    $this->assertTrue($status->isFailure());
    
    $status->setCode(Sabel_Response::INTERNAL_SERVER_ERROR);
    $this->assertTrue($status->isFailure());
    
    $status->setCode(Sabel_Response::FORBIDDEN);
    $this->assertTrue($status->isFailure());
  }
}
