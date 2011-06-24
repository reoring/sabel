<?php

/**
 * Sabel_Mail_MimeDecode
 *
 * @category  Mail
 * @package   org.sabel.mail
 * @author    Ebine Yutaka <ebine.yutaka@sabel.jp>
 * @copyright 2004-2008 Mori Reo <mori.reo@sabel.jp>
 * @license   http://www.opensource.org/licenses/bsd-license.php  BSD License
 */
class Sabel_Mail_MimeDecode extends Sabel_Object
{
  protected $isMbstringLoaded = false;
  
  public function __construct()
  {
    $this->isMbstringLoaded = extension_loaded("mbstring");
  }
  
  /**
   * @param string $source
   *
   * @return string
   */
  public function decode($source)
  {
    $mail = $this->toHeadersAndBody($source);
    
    if (empty($mail["header"])) {
      $mail["header"] = $mail["body"];
      $mail["body"] = "";
    }
    
    return $this->_decode($mail["header"], $mail["body"]);
  }
  
  protected function _decode($headerText, $body)
  {
    $headers = $this->createHeaders($headerText);
    $content = $this->createContentInfo($headers);
    
    $mail = new Sabel_Mail_Mime_Decoded();
    
    $mail->content     = $content;
    $mail->headers     = $headers;
    $mail->body        = null;
    $mail->html        = null;
    $mail->attachments = array();
    $mail->mails       = array();  // multipart/digest
    
    switch (strtolower($content->getType())) {
      /*
      case "multipart/parallel":
      case "multipart/report":
      case "multipart/signed":
        break;
      */
        
      case "multipart/mixed":
        $mixed = $this->_decodeMixedPart($content, $body);
        $mail->body = $mixed["body"];
        $mail->html = $mixed["html"];
        $mail->attachments = $mixed["attachments"];
        $mail->mails = $mixed["mails"];
        break;
        
      case "multipart/alternative":
        $alter = $this->_decodeAlternativePart($content, $body);
        $mail->body = $alter["body"];
        $mail->html = $alter["html"];
        break;
        
      case "multipart/related":
        $related = $this->_decodeRelatedPart($content, $body);
        $mail->body = $related["body"];
        $mail->html = $related["html"];
        break;
        
      case "multipart/digest":
        $mail->mails = $this->_decodeDigestPart($content, $body);
        break;
        
      default:  // simple mail.
        $part          = new stdClass();
        $part->body    = $body;
        $part->content = $content;
        $part->type    = $content->getType();
        
        if ($part->type === "text/html") {
          $mail->html = $this->createMimeObject($part);
        } else {
          $mail->body = $this->createMimeObject($part);
        }
        break;
    }
    
    return $mail;
  }
  
  protected function _decodeMixedPart(Sabel_Mail_Mime_Content $content, $body)
  {
    if (($boundary = $content->getBoundary()) === "") {
      $message = __METHOD__ . "() Boundary Not Found.";
      throw new Sabel_Mail_Exception($message);
    }
    
    $parts = $this->splitByBoundary($body, $boundary);
    $mixed = array("body" => null, "html" => null, "attachments" => array(), "mails" => array());
    $notBodyPart = false;
    
    foreach ($parts as $partOfMessage) {
      $part = $this->createPart($partOfMessage);
      
      switch ($part->type) {
        case "multipart/alternative":
          $alter = $this->_decodeAlternativePart($part->content, $part->body);
          $mixed["body"] = $alter["body"];
          $mixed["html"] = $alter["html"];
          break;
          
        case "multipart/related":
          $related = $this->_decodeRelatedPart($part->content, $part->body);
          if ($related["body"] !== null) $mixed["body"] = $related["body"];
          $mixed["html"] = $related["html"];
          break;
          
        case "multipart/digest":
          $mixed["mails"] = $this->_decodeDigestPart($part->content, $part->body);
          break;
          
        case "text/html":
          if (!$notBodyPart) {
            $mixed["html"] = $this->createMimeObject($part);
            break;
          }
          
        case "text/plain":
          if (!$notBodyPart) {
            $mixed["body"] = $this->createMimeObject($part);
            break;
          }
          
        default:
          $enc  = $part->content->getEncoding();
          $cset = $part->content->getCharset();
          $data = $this->decodeString($part->body, $enc, $cset, false);
          $file = new Sabel_Mail_Mime_File($part->content->getName(), $data, $part->type);
          $file->setCharset($cset);
          $file->setEncoding($enc);
          $file->setDisposition($part->content->getDisposition());
          $file->setHeaders($part->headers);
          $mixed["attachments"][] = $file;
      }
      
      $notBodyPart = true;
    }
    
    return $mixed;
  }
  
  protected function _decodeAlternativePart(Sabel_Mail_Mime_Content $content, $body)
  {
    if (($boundary = $content->getBoundary()) === "") {
      $message = __METHOD__ . "() Boundary Not Found.";
      throw new Sabel_Mail_Exception($message);
    }
    
    $parts = $this->splitByBoundary($body, $boundary);
    $alter = array("body" => null, "html" => null);
    
    foreach ($parts as $partOfMessage) {
      $part = $this->createPart($partOfMessage);
      
      switch ($part->type) {
        case "text/plain":
          $alter["body"] = $this->createMimeObject($part);
          break;
          
        case "text/html":
          $alter["html"] = $this->createMimeObject($part);
          break;
          
        case "multipart/related":
          $related = $this->_decodeRelatedPart($part->content, $part->body);
          if ($related["body"] !== null) $alter["body"] = $related["body"];
          $alter["html"] = $related["html"];
          break;
          
        default:
          $message = __METHOD__ . "() {$part->type} is not supported in multipart/alternative.";
          throw new Sabel_Mail_Mime_Exception($message);
      }
    }
    
    return $alter;
  }
  
  protected function _decodeRelatedPart(Sabel_Mail_Mime_Content $content, $body)
  {
    if (($boundary = $content->getBoundary()) === "") {
      $message = __METHOD__ . "() Boundary Not Found.";
      throw new Sabel_Mail_Exception($message);
    }
    
    $parts   = $this->splitByBoundary($body, $boundary);
    $related = array("body" => null, "html" => null);
    
    foreach ($parts as $partOfMessage) {
      $part = $this->createPart($partOfMessage);
      
      if ($part->type === "text/html") {
        $related["html"] = $this->createMimeObject($part);
      } elseif ($part->type === "multipart/alternative") {
        $alter = $this->_decodeAlternativePart($part->content, $part->body);
        $related["body"] = $alter["body"];
        $related["html"] = $alter["html"];
      } else {  // inline images.
        $enc  = $part->content->getEncoding();
        $body = $this->decodeString($part->body, $enc, $part->content->getCharset(), false);
        $cid  = (isset($part->headers["content-id"])) ? $part->headers["content-id"] : "";
        $related["html"]->addImage($cid, array(
          "data" => $body, "mimetype" => $part->type, "encoding" => $enc
        ));
      }
    }
    
    return $related;
  }
  
  protected function _decodeDigestPart(Sabel_Mail_Mime_Content $content, $body)
  {
    if (($boundary = $content->getBoundary()) === "") {
      $message = __METHOD__ . "() Boundary Not Found.";
      throw new Sabel_Mail_Exception($message);
    }
    
    $parts = $this->splitByBoundary($body, $boundary);
    $mails = array();
    
    foreach ($parts as $partOfMessage) {
      $part = $this->createPart($partOfMessage);
      if ($part->type === "message/rfc822") {
        $_part   = $this->toHeadersAndBody($part->body);
        $mails[] = $this->_decode($_part["header"], $_part["body"]);
      } else {
        $message = __METHOD__ . "() {$part->type} is not supported in multipart/digest.";
        throw new Sabel_Mail_Mime_Exception($message);
      }
    }
    
    return $mails;
  }
  
  protected function createMimeObject(stdClass $part)
  {
    $body    = $part->body;
    $content = $part->content;
    $ctype   = $part->type;
    $headers = (isset($part->headers)) ? $part->headers : array();
    
    if ($ctype === "text/plain" || $ctype === "text/html") {
      $cset = $content->getCharset();
      $enc  = $content->getEncoding();
      $body = $this->decodeString($body, $enc, $cset, false);
      $mime = ($ctype === "text/plain") ? new Sabel_Mail_Mime_Plain($body) : new Sabel_Mail_Mime_Html($body);
      $mime->setHeaders($headers);
      $mime->setCharset($cset);
      $mime->setEncoding($enc);
      $mime->setDisposition($content->getDisposition());
      
      return $mime;
    } else {
      $message = __METHOD__ . "() {$ctype} is not supported now.";
      throw new Sabel_Mail_Mime_Exception($message);
    }
  }
  
  protected function createPart($partOfMessage)
  {
    $part = new stdClass();
    $_tmp = $this->toHeadersAndBody($partOfMessage);
    
    $part->header  = $_tmp["header"];
    $part->body    = $_tmp["body"];
    $part->headers = $this->createHeaders($_tmp["header"]);
    $part->content = $this->createContentInfo($part->headers);
    $part->type    = $part->content->getType();
    
    return $part;
  }
  
  protected function createContentInfo($headers)
  {
    $content = new Sabel_Mail_Mime_Content();
    
    foreach ($headers as $key => $value) {
      switch ($key) {
        case "content-type":
          $values = $this->parseHeaderValue($value);
          $content->setType($values["value"]);
          if (isset($values["boundary"])) $content->setBoundary($values["boundary"]);
          if (isset($values["charset"])) $content->setCharset(strtoupper($values["charset"]));
          break;
          
        case "content-disposition":
          $values = $this->parseHeaderValue($value);
          $content->setDisposition($values["value"]);
          
          $filename = null;
          if (isset($values["filename"])) {
            $filename = $values["filename"];
          } elseif (isset($values["filename*"]) || isset($values["filename*0*"]) || isset($values["filename*0"])) {
            $buffer = array();
            foreach ($values as $k => $v) {
              if (strpos($k, "filename*") !== false) {
                $buffer[] = $v;
              }
            }
            
            $filename = implode("", $buffer);
          }
          
          if ($filename !== null) {
            $content->setName($this->decodeFileName($filename));
          }
          break;
          
        case "content-transfer-encoding":
          $values = $this->parseHeaderValue($value);
          $content->setEncoding($values["value"]);
          break;
      }
    }
    
    return $content;
  }
  
  /**
   * @param string $source
   *
   * @return array
   */
  public function toHeadersAndBody($source)
  {
    if (preg_match("/^(.+?)(\r\n\r\n|\n\n|\r\r)(.+)/s", $source, $matches) === 1) {
      $chars = (strlen($matches[2]) === 4) ? substr($matches[2], 0, 2) : $matches[2]{0};
      return array("header" => $matches[1], "body" => rtrim($matches[3], $chars));
    } else {
      return array("header" => "", "body" => $source);
    }
  }
  
  /**
   * @param string $headerText
   *
   * @return array
   */
  public function createHeaders($headerText)
  {
    $headers = array();
    if ($headerText === "") return $headers;
    
    preg_match("/(\r\n|\n|\r)/", $headerText, $matches);
    
    if (!isset($matches[0])) {
      $_tmp = array($headerText);
    } else {
      $eol = $matches[0];
      $headerText = preg_replace("/{$eol}(\t|\s)+/", " ", $headerText);
      $_tmp = explode($eol, $headerText);
    }
    
    foreach ($_tmp as $i => $line) {
      unset($_tmp[$i]);
      if ($line === "") break;
      
      @list ($key, $value) = explode(":", $line, 2);
      $value = $this->decodeHeader(ltrim($value));
      
      if (isset($headers[$key])) {
        if (is_array($headers[$key])) {
          $headers[$key][] = $value;
        } else {
          $headers[$key] = array($headers[$key], $value);
        }
      } else {
        $headers[$key] = $value;
      }
    }
    
    return array_change_key_case($headers);
  }
  
  /**
   * @param string $str
   *
   * @return array
   */
  public function parseHeaderValue($str)
  {
    $values = array();
    $values["params"] = array();
    
    if (($pos = strpos($str, ";")) === false) {
      $values["value"] = $str;
      return $values;
    }
    
    $regex = '/".+[^\\\\]"|\'.+[^\\\\]\'/U';
    $str = preg_replace_callback($regex, create_function('$matches', '
        return str_replace(";", "__%SC%__", $matches[0]);
    '), $str);
    
    $values["value"] = substr($str, 0, $pos);
    $str = ltrim(substr($str, $pos + 1));
    
    if ($str === "" || $str === ";") {
      return $values;
    }
    
    foreach (array_map("trim", explode(";", $str)) as $param) {
      if ($param === "") continue;
      @list ($key, $value) = explode("=", $param, 2);
      $key = strtolower($key);
      
      if ($value === null) {
        $values["params"][] = $key;
      } else {
        $quote = $value{0};
        if ($quote === '"' || $quote === "'") {
          if ($quote === substr($value, -1, 1)) {
            $value = str_replace("\\{$quote}", $quote, substr($value, 1, -1));
          }
        }
        
        $values[$key] = str_replace("__%SC%__", ";", $value);
      }
    }
    
    return $values;
  }
  
  /**
   * @param string $body
   * @param string $boundary
   *
   * @return array
   */
  public function splitByBoundary($body, $boundary)
  {
    $parts = array_map("ltrim", explode("--" . $boundary, $body));
    
    array_shift($parts);
    array_pop($parts);
    
    return $parts;
  }
  
  /**
   * @param string $str
   *
   * @return string
   */
  public function decodeHeader($str)
  {
    $regex = "/=\?([^?]+)\?(q|b)\?([^?]*)\?=/i";
    $count = preg_match_all($regex, $str, $matches);
    if ($count < 1) return $str;
    
    $str = str_replace("?= =?", "?==?", $str);
    
    for ($i = 0; $i < $count; $i++) {
      $encoding = (strtolower($matches[2][$i]) === "b") ? "base64" : "quoted-printable";
      $value = $this->decodeString($matches[3][$i], $encoding, $matches[1][$i]);
      $str = str_replace($matches[0][$i], $value, $str);
    }
    
    return $str;
  }
  
  /**
   * @param string $filename
   *
   * @return string
   */
  public function decodeFileName($filename)
  {
    if (preg_match("/^([a-zA-Z0-9\-]+)'([a-z]{2-5})?'(%.+)$/", $filename, $matches) === 1) {  // RFC2231
      return $this->decodeString(urldecode($matches[3]), "", $matches[1]);
    } elseif (preg_match("/=\?([^?]+)\?(q|b)\?([^?]*)\?=/i", $filename, $matches) === 1) {
      $encoding = (strtolower($matches[2]) === "b") ? "base64" : "quoted-printable";
      return $this->decodeString($matches[3], $encoding, $matches[1]);
    } else {
      return $filename;
    }
  }
  
  /**
   * @param string $str
   * @param string $encoding
   * @param string $charset
   *
   * @return string
   */
  public function decodeString($str, $encoding, $charset, $isHeader = true)
  {
    switch (strtolower($encoding)) {
      case "base64":
        $str = base64_decode($str);
        break;
        
      case "quoted-printable":
        $str = Sabel_Mail_QuotedPrintable::decode($str, $isHeader);
        break;
    }
    
    if ($this->isMbstringLoaded && $charset) {
      return $this->mbConvertEncoding($str, $charset);
    } else {
      return $str;
    }
  }
  
  /**
   * @param string $str
   * @param string $fromEnc
   *
   * @return string
   */
  protected function mbConvertEncoding($str, $fromEnc)
  {
    static $internalEncoding = null;
    
    if ($internalEncoding === null) {
      $internalEncoding = strtoupper(mb_internal_encoding());
    }
    
    $fromEnc = strtoupper($fromEnc);
    if ($internalEncoding === $fromEnc) {
      return $str;
    } else {
      return mb_convert_encoding($str, $internalEncoding, $fromEnc);
    }
  }
}
