<?php

/**
 * test for sabel.response.header.Cli
 * using classes: sabel.response.Object
 *
 * @category Response
 * @author   Ebine Yutaka <ebine.yutaka@sabel.jp>
 */
class Test_Response_Header extends SabelTestCase
{
  public static function suite()
  {
    return self::createSuite("Test_Response_Header");
  }
  
  public function testOutputHeader()
  {
    $response = new Sabel_Response_Object();
    $response->setHeader("Content-Type",   "text/html; charset=UTF-8");
    $response->setHeader("Content-Length", "4096");
    
    $headers = $response->outputHeader();
    $this->assertEquals("Content-Type: text/html; charset=UTF-8", $headers[1]);
    $this->assertEquals("Content-Length: 4096", $headers[2]);
  }
  
  public function testOutputStatus()
  {
    $response = new Sabel_Response_Object();
    $response->getStatus()->setCode(Sabel_Response::FORBIDDEN);
    $headers = $response->outputHeader();
    $this->assertEquals("HTTP/1.0 403 Forbidden", $headers[0]);
    
    $response = new Sabel_Response_Object();
    $response->getStatus()->setCode(Sabel_Response::NOT_MODIFIED);
    $headers = $response->outputHeader();
    $this->assertEquals("HTTP/1.0 304 Not Modified", $headers[0]);
  }
}
