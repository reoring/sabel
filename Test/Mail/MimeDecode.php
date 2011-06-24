<?php

/**
 * test case for Sabel_Mail_MimeDecode
 *
 * @category  Mail
 * @author    Ebine Yutaka <ebine.yutaka@sabel.jp>
 */
class Test_Mail_MimeDecode extends SabelTestCase
{
  public static function suite()
  {
    return self::createSuite("Test_Mail_MimeDecode");
  }
  
  /**
   * @test
   */
  public function plain()
  {
    $decoded = $this->decode("plain");
    
    $this->assertEquals("text/plain", $decoded->content->getType());
    $this->assertEquals("ISO-2022-JP", $decoded->content->getCharset());
    
    $this->assertEquals('"from1" <from1@example.com>', $decoded->getHeader("From"));
    $this->assertEquals('from1', $decoded->getFromName());
    $this->assertEquals('from1@example.com', $decoded->getFromAddr());
    
    $this->assertEquals('to1@example.com', $decoded->getToAddr());
    
    $this->assertEquals("件名", $decoded->getHeader("Subject"));
    $this->assertNotEquals(false, strpos($decoded->body->getContent(), "本文"));
    
    $this->assertNull($decoded->html);
    $this->assertEquals(array(), $decoded->attachments);
  }
  
  /**
   * @test
   */
  public function html()
  {
    $decoded = $this->decode("html");
    
    $this->assertEquals("text/html", $decoded->content->getType());
    $this->assertEquals("ISO-2022-JP", $decoded->content->getCharset());
    
    $this->assertEquals('"from2" <from2@example.com>', $decoded->getHeader("From"));
    $this->assertEquals('from2', $decoded->getFromName());
    $this->assertEquals('from2@example.com', $decoded->getFromAddr());
    
    $this->assertEquals('to2@example.com', $decoded->getToAddr());
    
    $this->assertEquals("件名", $decoded->getHeader("Subject"));
    $this->assertNotEquals(false, stripos($decoded->html->getContent(), "<html>"));
    
    $this->assertNull($decoded->body);
    $this->assertEquals(array(), $decoded->html->getImages());
    $this->assertEquals(array(), $decoded->attachments);
  }
  
  /**
   * @test
   */
  public function plain_html()
  {
    $decoded = $this->decode("plain_html");
    
    $this->assertEquals("multipart/alternative", $decoded->content->getType());
    
    $this->assertEquals('"from3" <from3@example.com>', $decoded->getHeader("From"));
    $this->assertEquals('from3', $decoded->getFromName());
    $this->assertEquals('from3@example.com', $decoded->getFromAddr());
    
    $this->assertEquals('to3@example.com', $decoded->getToAddr());
    
    $this->assertEquals("件名", $decoded->getHeader("Subject"));
    $this->assertNotEquals(false, strpos($decoded->body->getContent(), "本文"));
    $this->assertNotEquals(false, stripos($decoded->html->getContent(), "<html>"));
    
    $this->assertEquals(array(), $decoded->html->getImages());
    $this->assertEquals(array(), $decoded->attachments);
  }
  
  /**
   * @test
   */
  public function htmlimg()
  {
    $decoded = $this->decode("htmlimg");
    
    $this->assertEquals("multipart/related", $decoded->content->getType());
    
    $this->assertEquals('"from4" <from4@example.com>', $decoded->getHeader("From"));
    $this->assertEquals('from4', $decoded->getFromName());
    $this->assertEquals('from4@example.com', $decoded->getFromAddr());
    
    $this->assertEquals('to4@example.com', $decoded->getToAddr());
    
    $this->assertEquals("件名", $decoded->getHeader("Subject"));
    $this->assertNotEquals(false, stripos($decoded->html->getContent(), "<html>"));
    
    $images = $decoded->html->getImages();
    $this->assertEquals(2, count($images));
    $this->assertEquals("image/png", $images[0]["mimetype"]);
    $this->assertEquals("image/png", $images[1]["mimetype"]);
    $this->assertTrue($images[0]["data"] !== $images[1]["data"]);
    
    $this->assertNull($decoded->body);
    $this->assertEquals(array(), $decoded->attachments);
  }
  
  /**
   * @test
   */
  public function plain_attachment()
  {
    $decoded = $this->decode("plain_attachment");
    
    $this->assertEquals("multipart/mixed", $decoded->content->getType());
    
    $this->assertEquals('"from5" <from5@example.com>', $decoded->getHeader("From"));
    $this->assertEquals('from5', $decoded->getFromName());
    $this->assertEquals('from5@example.com', $decoded->getFromAddr());
    
    $this->assertEquals('to5@example.com', $decoded->getToAddr());
    
    $this->assertEquals("件名", $decoded->getHeader("Subject"));
    $this->assertNotEquals(false, strpos($decoded->body->getContent(), "本文"));
    
    $this->assertNull($decoded->html);
    
    $attachments = $decoded->attachments;
    $this->assertEquals(2, count($attachments));
    $this->assertEquals("image/png", $attachments[0]->getType());
    $this->assertEquals("image/png", $attachments[1]->getType());
    $this->assertTrue($attachments[0]->getContent() !== $attachments[1]->getContent());
  }
  
  /**
   * @test
   */
  public function plain_html_attachment()
  {
    $decoded = $this->decode("plain_html_attachment");
    
    $this->assertEquals("multipart/mixed", $decoded->content->getType());
    
    $this->assertEquals('"from6" <from6@example.com>', $decoded->getHeader("From"));
    $this->assertEquals('from6', $decoded->getFromName());
    $this->assertEquals('from6@example.com', $decoded->getFromAddr());
    
    $this->assertEquals('to6@example.com', $decoded->getToAddr());
    
    $this->assertEquals("件名", $decoded->getHeader("Subject"));
    $this->assertNotEquals(false, strpos($decoded->body->getContent(), "本文"));
    $this->assertNotEquals(false, stripos($decoded->html->getContent(), "<html>"));
    $this->assertEquals(array(), $decoded->html->getImages());
    
    $attachments = $decoded->attachments;
    $this->assertEquals(2, count($attachments));
    $this->assertEquals("image/png", $attachments[0]->getType());
    $this->assertEquals("image/png", $attachments[1]->getType());
    $this->assertTrue($attachments[0]->getContent() !== $attachments[1]->getContent());
  }
  
  /**
   * @test
   */
  public function html_attachment()
  {
    $decoded = $this->decode("html_attachment");
    
    $this->assertEquals("multipart/mixed", $decoded->content->getType());
    
    $this->assertEquals('"from7" <from7@example.com>', $decoded->getHeader("From"));
    $this->assertEquals('from7', $decoded->getFromName());
    $this->assertEquals('from7@example.com', $decoded->getFromAddr());
    
    $this->assertEquals('to7@example.com', $decoded->getToAddr());
    
    $this->assertEquals("件名", $decoded->getHeader("Subject"));
    $this->assertNotEquals(false, stripos($decoded->html->getContent(), "<html>"));
    
    $this->assertNull($decoded->body);
    
    $attachments = $decoded->attachments;
    $this->assertEquals(2, count($attachments));
    $this->assertEquals("image/png", $attachments[0]->getType());
    $this->assertEquals("image/png", $attachments[1]->getType());
    $this->assertTrue($attachments[0]->getContent() !== $attachments[1]->getContent());
  }
  
  /**
   * @test
   */
  public function htmlimg_attachment()
  {
    $decoded = $this->decode("htmlimg_attachment");
    
    $this->assertEquals("multipart/mixed", $decoded->content->getType());
    
    $this->assertEquals('"from8" <from8@example.com>', $decoded->getHeader("From"));
    $this->assertEquals('from8', $decoded->getFromName());
    $this->assertEquals('from8@example.com', $decoded->getFromAddr());
    
    $this->assertEquals('to8@example.com', $decoded->getToAddr());
    
    $this->assertEquals("件名", $decoded->getHeader("Subject"));
    $this->assertNotEquals(false, stripos($decoded->html->getContent(), "<html>"));
    
    $images = $decoded->html->getImages();
    $this->assertEquals(2, count($images));
    $this->assertEquals("image/png", $images[0]["mimetype"]);
    $this->assertEquals("image/png", $images[1]["mimetype"]);
    $this->assertTrue($images[0]["data"] !== $images[1]["data"]);
    
    $this->assertNull($decoded->body);
    
    $attachments = $decoded->attachments;
    $this->assertEquals(2, count($attachments));
    $this->assertEquals("image/png", $attachments[0]->getType());
    $this->assertEquals("image/png", $attachments[1]->getType());
    $this->assertTrue($attachments[0]->getContent() !== $attachments[1]->getContent());
  }
  
  /**
   * @test
   */
  public function plain_htmlimg()  // thunderbird
  {
    $decoded = $this->decode("plain_htmlimg");
    
    $this->assertEquals("multipart/alternative", $decoded->content->getType());
    
    $this->assertEquals('"from9" <from9@example.com>', $decoded->getHeader("From"));
    $this->assertEquals('from9', $decoded->getFromName());
    $this->assertEquals('from9@example.com', $decoded->getFromAddr());
    
    $this->assertEquals('to9@example.com', $decoded->getToAddr());
    
    $this->assertEquals("件名", $decoded->getHeader("Subject"));
    $this->assertNotEquals(false, strpos($decoded->body->getContent(), "本文"));
    $this->assertNotEquals(false, stripos($decoded->html->getContent(), "<html>"));
    
    $images = $decoded->html->getImages();
    $this->assertEquals(2, count($images));
    $this->assertEquals("image/png", $images[0]["mimetype"]);
    $this->assertEquals("image/png", $images[1]["mimetype"]);
    $this->assertTrue($images[0]["data"] !== $images[1]["data"]);
    
    $this->assertEquals(array(), $decoded->attachments);
  }
  
  /**
   * @test
   */
  public function plain_htmlimg2()  // outlook express
  {
    $decoded = $this->decode("plain_htmlimg2");
    
    $this->assertEquals("multipart/related", $decoded->content->getType());
    
    $this->assertEquals('"from10" <from10@example.com>', $decoded->getHeader("From"));
    $this->assertEquals('from10', $decoded->getFromName());
    $this->assertEquals('from10@example.com', $decoded->getFromAddr());
    
    $this->assertEquals('to10@example.com', $decoded->getToAddr());
    
    $this->assertEquals("件名", $decoded->getHeader("Subject"));
    $this->assertNotEquals(false, strpos($decoded->body->getContent(), "本文"));
    $this->assertNotEquals(false, stripos($decoded->html->getContent(), "<html>"));
    
    $images = $decoded->html->getImages();
    $this->assertEquals(2, count($images));
    $this->assertEquals("image/png", $images[0]["mimetype"]);
    $this->assertEquals("image/png", $images[1]["mimetype"]);
    $this->assertTrue($images[0]["data"] !== $images[1]["data"]);
    
    $this->assertEquals(array(), $decoded->attachments);
  }
  
  /**
   * @test
   */
  public function plain_htmlimg_attachment()  // thunderbird
  {
    $decoded = $this->decode("plain_htmlimg_attachment");
    
    $this->assertEquals("multipart/mixed", $decoded->content->getType());
    
    $this->assertEquals('"from11" <from11@example.com>', $decoded->getHeader("From"));
    $this->assertEquals('from11', $decoded->getFromName());
    $this->assertEquals('from11@example.com', $decoded->getFromAddr());
    
    $this->assertEquals('to11@example.com', $decoded->getToAddr());
    
    $this->assertEquals("件名", $decoded->getHeader("Subject"));
    $this->assertNotEquals(false, strpos($decoded->body->getContent(), "本文"));
    $this->assertNotEquals(false, stripos($decoded->html->getContent(), "<html>"));
    
    $images = $decoded->html->getImages();
    $this->assertEquals(2, count($images));
    $this->assertEquals("image/png", $images[0]["mimetype"]);
    $this->assertEquals("image/png", $images[1]["mimetype"]);
    $this->assertTrue($images[0]["data"] !== $images[1]["data"]);
    
    $attachments = $decoded->attachments;
    $this->assertEquals(2, count($attachments));
    $this->assertEquals("image/png", $attachments[0]->getType());
    $this->assertEquals("image/png", $attachments[1]->getType());
    $this->assertTrue($attachments[0]->getContent() !== $attachments[1]->getContent());
  }
  
  /**
   * @test
   */
  public function plain_htmlimg_attachment2()  // outlook express
  {
    $decoded = $this->decode("plain_htmlimg_attachment2");
    
    $this->assertEquals("multipart/mixed", $decoded->content->getType());
    
    $this->assertEquals('"from12" <from12@example.com>', $decoded->getHeader("From"));
    $this->assertEquals('from12', $decoded->getFromName());
    $this->assertEquals('from12@example.com', $decoded->getFromAddr());
    
    $this->assertEquals('to12@example.com', $decoded->getToAddr());
    
    $this->assertEquals("件名", $decoded->getHeader("Subject"));
    $this->assertNotEquals(false, strpos($decoded->body->getContent(), "本文"));
    $this->assertNotEquals(false, stripos($decoded->html->getContent(), "<html>"));
    
    $images = $decoded->html->getImages();
    $this->assertEquals(2, count($images));
    $this->assertEquals("image/png", $images[0]["mimetype"]);
    $this->assertEquals("image/png", $images[1]["mimetype"]);
    $this->assertTrue($images[0]["data"] !== $images[1]["data"]);
    
    $attachments = $decoded->attachments;
    $this->assertEquals(2, count($attachments));
    $this->assertEquals("image/png", $attachments[0]->getType());
    $this->assertEquals("image/png", $attachments[1]->getType());
    $this->assertTrue($attachments[0]->getContent() !== $attachments[1]->getContent());
  }
  
  /**
   * @test
   */
  public function jpattachment()  // thunderbird (RFC2231)
  {
    $decoded = $this->decode("jpattachment");
    
    $attachment = $decoded->attachments[0];
    $this->assertEquals("日本語日本語日本語日本語日本語日本語.png", $attachment->getName());
  }
  
  /**
   * @test
   */
  public function jpattachment2()  // outlook express (mime encoding)
  {
    $decoded = $this->decode("jpattachment2");
    
    $attachment = $decoded->attachments[0];
    $this->assertEquals("日本語日本語日本語日本語日本語日本語.png", $attachment->getName());
  }
  
  protected function decode($name)
  {
    $decoder = new Sabel_Mail_MimeDecode();
    return $decoder->decode(file_get_contents(dirname(__FILE__) . DS . "mails" . DS . $name . ".eml"));
  }
}
