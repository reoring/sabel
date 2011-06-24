<?php

/**
 * Sabel_Mail_Mime_Abstract
 *
 * @category   Mail
 * @package    org.sabel.mail
 * @author     Ebine Yutaka <ebine.yutaka@sabel.jp>
 * @copyright  2004-2008 Mori Reo <mori.reo@sabel.jp>
 * @license    http://www.opensource.org/licenses/bsd-license.php  BSD License
 */
abstract class Sabel_Mail_Mime_Abstract
{
  /**
   * @var array
   */
  protected $headers = array();
  
  /**
   * @var string
   */
  protected $charset = "ISO-8859-1";
  
  /**
   * @var string
   */
  protected $content = "";
  
  /**
   * @var string
   */
  protected $encoding = "7bit";
  
  /**
   * @var string
   */
  protected $disposition = "inline";
  
  /**
   * @param array $headers
   *
   * @return void
   */
  public function setHeaders(array $headers)
  {
    $this->headers = $headers;
  }
  
  /**
   * @param string $name
   *
   * @return string
   */
  public function getHeader($name)
  {
    $lowered = strtolower($name);
    if (isset($this->headers[$lowered])) {
      return $this->headers[$lowered];
    } else {
      return "";
    }
  }
  
  /**
   * @return const Sabel_Mail_Mime_Abstract
   */
  public function getType()
  {
    return $this->type;
  }
  
  /**
   * @param string $content
   *
   * @return void
   */
  public function setContent($content)
  {
    $this->content = $content;
  }
  
  /**
   * @return string
   */
  public function getContent()
  {
    return $this->content;
  }
  
  /**
   * @param string $charset
   *
   * @return void
   */
  public function setCharset($charset)
  {
    $this->charset = $charset;
  }
  
  /**
   * @return string
   */
  public function getCharset()
  {
    return $this->charset;
  }
  
  /**
   * @param string $encoding
   *
   * @return void
   */
  public function setEncoding($encoding)
  {
    $this->encoding = strtolower($encoding);
  }
  
  /**
   * @return string
   */
  public function getEncoding()
  {
    return $this->encoding;
  }
  
  /**
   * @param string $disposition
   *
   * @return void
   */
  public function setDisposition($disposition)
  {
    $this->disposition = strtolower($disposition);
  }
  
  /**
   * @return string
   */
  public function getDisposition()
  {
    return $this->disposition;
  }
  
  /**
   * @return string
   */
  public function getEncodedContent()
  {
    $content = $this->content;
    if (extension_loaded("mbstring")) {
      $content = mb_convert_encoding($content, $this->charset);
    }
    
    return $this->encode($content, $this->encoding, Sabel_Mail::getEol());
  }
  
  protected function encode($str, $encoding, $eol = "\r\n", $length = Sabel_Mail::LINELENGTH)
  {
    switch (strtolower($encoding)) {
      case "base64":
        return rtrim(chunk_split(base64_encode($str), $length, $eol));
      case "quoted-printable":
        return Sabel_Mail_QuotedPrintable::encode($str, $length, $eol);
      default:
        return $str;
    }
  }
}
