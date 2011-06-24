<?php

/**
 * Sabel_Mail
 *
 * @category   Mail
 * @package    org.sabel.mail
 * @author     Ebine Yutaka <ebine.yutaka@sabel.jp>
 * @copyright  2004-2008 Mori Reo <mori.reo@sabel.jp>
 * @license    http://www.opensource.org/licenses/bsd-license.php  BSD License
 */
class Sabel_Mail extends Sabel_Object
{
  const LINELENGTH = 74;
  
  /**
   * @var Sabel_Mail_Sender_Interface
   */
  protected $sender = null;
  
  /**
   * @var Sabel_Mail_Mime_Plain
   */
  protected $body = null;
  
  /**
   * @var Sabel_Mail_Mime_Html
   */
  protected $html = null;
  
  /**
   * @var string
   */
  protected $boundary = "";
  
  /**
   * @var array
   */
  protected $headers = array();
  
  /**
   * @var array
   */
  protected $attachments = array();
  
  /**
   * @var boolean
   */
  protected $headerEncoding = "base64";
  
  /**
   * @var boolean
   */
  protected $isMbstringLoaded = false;
  
  /**
   * @var string
   */
  protected static $EOL = "\r\n";
  
  public function __construct($charset = "ISO-8859-1", $eol = "\r\n")
  {
    self::$EOL = $eol;
    
    $this->charset = $charset;
    $this->isMbstringLoaded = extension_loaded("mbstring");
  }
  
  public static function setEol($eol)
  {
    self::$EOL = $eol;
  }
  
  public static function getEol()
  {
    return self::$EOL;
  }
  
  public function setCharset($charset)
  {
    $this->charset = $charset;
  }
  
  public function getCharset()
  {
    return $this->charset;
  }
  
  public function setSender(Sabel_Mail_Sender_Interface $sender)
  {
    $this->sender = $sender;
  }
  
  public function setHeaderEncoding($encoding)
  {
    $this->headerEncoding = strtolower($encoding);
  }
  
  public function setFrom($from, $name = "")
  {
    if ($name === "") {
      $this->headers["From"] = array("address" => $from, "name" => "");
    } else {
      $this->headers["From"] = array("address" => $from, "name" => $this->encodeHeader($name));
    }
    
    return $this;
  }
  
  public function setBoundary($boundary)
  {
    $this->boundary = $boundary;
    
    return $this;
  }
  
  public function getBoundary()
  {
    if ($this->boundary === "") {
      return $this->boundary = md5hash();
    } else {
      return $this->boundary;
    }
  }
  
  public function addTo($to, $name = "")
  {
    if ($name === "") {
      $to = array("address" => $to, "name" => "");
    } else {
      $to = array("address" => $to, "name" => $this->encodeHeader($name));
    }
    
    if (isset($this->headers["To"])) {
      $this->headers["To"][] = $to;
    } else {
      $this->headers["To"] = array($to);
    }
    
    return $this;
  }
  
  public function setTo($to)
  {
    $this->headers["To"] = array();
    
    if (is_string($to)) {
      $this->headers["To"] = array(array("address" => $to, "name" => ""));
    } elseif (is_array($to)) {
      foreach ($to as $recipient) {
        $this->headers["To"][] = array("address" => $recipient, "name" => "");
      }
    } else {
      $message = __METHOD__ . "() argument must be a string or an array.";
      throw new Sabel_Exception_InvalidArgument($message);
    }
  }
  
  /**
   * set a 'Reply-To' header to this mail.
   *
   * @param string $replyTo Reply-To address
   */
  public function setReplyTo($replyTo)
  {
    if (is_string($replyTo)) {
      $this->headers["Reply-To"] = $replyTo;
    } else {
      $message = __METHOD__ . "() argument must be a string.";
      throw new Sabel_Exception_InvalidArgument($message);
    }
  }
  
  public function addCc($to, $name = "")
  {
    if ($name === "") {
      $to = array("address" => $to, "name" => "");
    } else {
      $to = array("address" => $to, "name" => $this->encodeHeader($name));
    }
    
    if (isset($this->headers["Cc"])) {
      $this->headers["Cc"][] = $to;
    } else {
      $this->headers["Cc"] = array($to);
    }
    
    return $this;
  }
  
  public function addBcc($to)
  {
    if (isset($this->headers["Bcc"])) {
      $this->headers["Bcc"][] = $to;
    } else {
      $this->headers["Bcc"] = array($to);
    }
    
    return $this;
  }
  
  public function setSubject($subject)
  {
    $this->headers["Subject"] = $this->encodeHeader($subject);
    
    return $this;
  }
  
  public function setBody($text, $encoding = "7bit", $disposition = "inline")
  {
    $this->body = new Sabel_Mail_Mime_Plain($text);
    $this->body->setCharset($this->charset);
    $this->body->setEncoding($encoding);
    $this->body->setDisposition($disposition);
    
    return $this;
  }
  
  public function setHtml($text, $encoding = "7bit", $disposition = "inline")
  {
    if ($text instanceof Sabel_Mail_Mime_Html) {
      $this->html = $text;
    } elseif (is_string($text)) {
      $this->html = $this->createHtmlPart($text, $encoding, $disposition);
    } else {
      $message = __METHOD__ . "() argument must be a string or "
               . "an instance of Sabel_Mail_Mime_Html";
      
      throw new Sabel_Exception_InvalidArgument($message);
    }
    
    return $this;
  }
  
  public function createHtmlPart($text, $encoding = "7bit", $disposition = "inline")
  {
    $html = new Sabel_Mail_Mime_Html($text);
    $html->setCharset($this->charset);
    $html->setEncoding($encoding);
    $html->setDisposition($disposition);
    
    return $html;
  }
  
  public function attach($name, $data, $mimeType = null, $encoding = "base64", $followRFC2231 = false)
  {
    if ($mimeType === null) {
      $mimeType = get_mime_type($data);
      if (!$mimeType) $mimeType = "application/octet-stream";
    }
    
    if (is_readable($data)) {
      $data = file_get_contents($data);
    }
    
    $file = new Sabel_Mail_Mime_File($name, $data, $mimeType, $followRFC2231);
    $file->setCharset($this->charset);
    $file->setEncoding($encoding);
    $file->setDisposition("attachment");
    
    $this->attachments[] = $file;
    
    return $this;
  }
  
  public function generateContentId()
  {
    $fromAddress = $this->headers["From"]["address"];
    $this->checkAddressFormat($fromAddress);
    list (, $host) = explode("@", $fromAddress);
    return md5hash() . "@" . $host;
  }
  
  public function addHeader($name, $value)
  {
    $this->headers[$name] = $value;
    
    return $this;
  }
  
  public function getHeader($name)
  {
    if (isset($this->headers[$name])) {
      return $this->headers[$name];
    } else {
      return null;
    }
  }
  
  public function getHeaders()
  {
    return $this->headers;
  }
  
  public function encodeHeader($header)
  {
    if ($this->isMbstringLoaded) {
      $enc = ($this->headerEncoding === "base64") ? "B" : "Q";
      return mb_encode_mimeheader($header, $this->charset, $enc, self::$EOL);
    } elseif ($this->headerEncoding === "base64") {
      return "=?{$this->charset}?B?" . base64_encode($header) . "?=";
    } else {
      $quoted = Sabel_Mail_QuotedPrintable::encode($header, self::LINELENGTH, self::$EOL);
      return "=?{$this->charset}?Q?{$quoted}?=";
    }
  }
  
  public function send(array $options = array())
  {
    if ($this->sender === null) {
      $this->sender = new Sabel_Mail_Sender_PHP();
    }
    
    $bodyText = $this->createBodyText();
    $headers  = $this->_setBasicHeaders($this->headers);
    
    return $this->sender->send($headers, $bodyText, $options);
  }
  
  protected function createBodyText()
  {
    // empty body.
    if ($this->body === null && $this->html === null) {
      $message = __METHOD__ . "() empty body.";
      throw new Sabel_Mail_Exception($message);
    }
    
    $boundary  = $this->getBoundary();
    $boundary2 = md5hash();
    $body = array("--{$boundary}");
    
    list ($hasAttachment, $hasInlineImage) = $this->_setContentType($boundary);
    
    if ($this->body !== null && $this->html !== null) {  // plain & html texts.
      if ($hasAttachment && $hasInlineImage) {
        $boundary3 = md5hash();
        $body[] = 'Content-Type: multipart/alternative; boundary="' . $boundary2 . '"' . self::$EOL;
        $body[] = "--{$boundary2}";
        $body[] = $this->body->toMailPart();
        $body[] = "--{$boundary2}";
        $body[] = 'Content-Type: multipart/related; boundary="' . $boundary3 . '"' . self::$EOL;
        $body[] = "--{$boundary3}";
        $body[] = $this->html->toMailPart($boundary3);
        $body[] = "--{$boundary3}--" . self::$EOL;
        $body[] = "--{$boundary2}--" . self::$EOL;
        $body[] = $this->createAttachmentText($boundary);
      } elseif ($hasInlineImage) {
        $body[] = $this->body->toMailPart();
        $body[] = "--{$boundary}";
        $body[] = 'Content-Type: multipart/related; boundary="' . $boundary2 . '"' . self::$EOL;
        $body[] = "--{$boundary2}";
        $body[] = $this->html->toMailPart($boundary2);
        $body[] = "--{$boundary2}--" . self::$EOL;
      } elseif ($hasAttachment) {
        $body[] = 'Content-Type: multipart/alternative; boundary="' . $boundary2 . '"' . self::$EOL;
        $body[] = "--{$boundary2}";
        $body[] = $this->body->toMailPart();
        $body[] = "--{$boundary2}";
        $body[] = $this->html->toMailPart();
        $body[] = "--{$boundary2}--" . self::$EOL;
        $body[] = $this->createAttachmentText($boundary);
      } else {
        $body[] = $this->body->toMailPart();
        $body[] = "--{$boundary}";
        $body[] = $this->html->toMailPart();
      }
      
      $body[] = "--{$boundary}--";
      return implode(self::$EOL, $body);
    } elseif ($this->html !== null) {  // only html text.
      if ($hasAttachment && $hasInlineImage) {
        $body[] = 'Content-Type: multipart/related; boundary="' . $boundary2 . '"' . self::$EOL;
        $body[] = "--{$boundary2}";
        $body[] = $this->html->toMailPart($boundary2);
        $body[] = "--{$boundary2}--" . self::$EOL;
        $body[] = $this->createAttachmentText($boundary);
      } elseif ($hasInlineImage) {
        $body[] = $this->html->toMailPart($boundary);
      } elseif ($hasAttachment) {
        $body[] = $this->html->toMailPart();
        $body[] = $this->createAttachmentText($boundary);
      } else {
        $this->headers["Content-Transfer-Encoding"] = $this->html->getEncoding();
        return $this->html->toMailPart();
      }
      
      $body[] = "--{$boundary}--";
      return implode(self::$EOL, $body);
    } else {  // only plain text.
      if ($hasAttachment) {
        $body   = array("--{$boundary}");
        $body[] = $this->body->toMailPart();
        $body[] = $this->createAttachmentText($boundary);
        $body[] = "--{$boundary}--";
        
        return implode(self::$EOL, $body);
      } else {
        $this->headers["Content-Transfer-Encoding"] = $this->body->getEncoding();
        return $this->body->getEncodedContent();
      }
    }
  }
  
  protected function _setBasicHeaders(array $headers)
  {
    $hasMessageId  = false;
    $hasMimeHeader = false;
    $hasDate       = false;
    
    foreach ($headers as $name => $header) {
      $lowered = strtolower($name);
      if ($lowered === "message-id") {
        $hasMessageId = true;
      } elseif ($lowered === "mime-version") {
        $hasMimeHeader = true;
      } elseif ($lowered === "date") {
        $hasDate = true;
      }
    }
    
    if (!$hasMessageId) {
      $fromAddress = $headers["From"]["address"];
      $this->checkAddressFormat($fromAddress);
      list (, $host) = explode("@", $fromAddress);
      $headers["Message-ID"] = "<" . md5hash() . "@{$host}>";
    }
    
    if (!$hasMimeHeader) {
      $headers["Mime-Version"] = "1.0";
    }
    
    if (!$hasDate) {
      $headers["Date"] = date("r");
    }
    
    return $headers;
  }
  
  protected function _setContentType($boundary)
  {
    $hasAttachment  = (count($this->attachments) > 0);
    $hasInlineImage = (is_object($this->html) && $this->html->hasImage());
    
    if ($hasAttachment) {
      $this->headers["Content-Type"] = 'multipart/mixed; boundary="' . $boundary . '"';
    } elseif ($this->body !== null && $this->html !== null) {
      $this->headers["Content-Type"] = 'multipart/alternative; boundary="' . $boundary . '"';
    } elseif ($this->html !== null && $hasInlineImage) {
      $this->headers["Content-Type"] = 'multipart/related; boundary="' . $boundary . '"';
    } else {
      $body = ($this->body !== null) ? $this->body : $this->html;
      $this->headers["Content-Type"] = $body->getType() . "; charset=" . $this->charset;
    }
    
    return array($hasAttachment, $hasInlineImage);
  }
  
  protected function createAttachmentText($boundary)
  {
    $texts = array();
    foreach ($this->attachments as $attachment) {
      $texts[] = "--{$boundary}";
      $texts[] = $attachment->toMailPart();
    }
    
    return implode(self::$EOL, $texts);
  }
  
  protected function checkAddressFormat($address)
  {
    if (strpos($address, "@") === false) {
      throw new Sabel_Mail_Exception("atmark not found in address: " . $address);
    }
  }
}
