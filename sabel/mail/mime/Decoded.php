<?php

/**
 * Sabel_Mail_Mime_File
 *
 * @category   Mail
 * @package    org.sabel.mail
 * @author     Ebine Yutaka <ebine.yutaka@sabel.jp>
 * @copyright  2004-2008 Mori Reo <mori.reo@sabel.jp>
 * @license    http://www.opensource.org/licenses/bsd-license.php  BSD License
 */
class Sabel_Mail_Mime_Decoded extends Sabel_Object
{
  public $content     = null;
  public $headers     = array();
  public $body        = null;
  public $html        = null;
  public $attachments = array();
  public $mails       = array();  // multipart/digest
  
  public function getHeader($name)
  {
    $lowered = strtolower($name);
    
    if (isset($this->headers[$lowered])) {
      return $this->headers[$lowered];
    } else {
      return "";
    }
  }
  
  public function getFromAddr()
  {
    $from = $this->getHeader("From");
    if ($from === "") return "";
    
    preg_match('/<([^@]+@[^@]+)>/', $from, $matches);
    return (isset($matches[1])) ? $matches[1] : $from;
  }
  
  public function getFromName()
  {
    $from = $this->getHeader("From");
    if ($from === "") return "";
    
    return $this->removeQuote(preg_replace('/<[^@]+@[^@]+>/', "", $from));
  }
  
  public function getToAddr()
  {
    $to = $this->getHeader("To");
    if ($to === "") return "";
    
    if (strpos($to, ",") === false) {
      preg_match('/<([^@]+@[^@]+)>/', $to, $matches);
      return (isset($matches[1])) ? $matches[1] : $to;
    } else {
      $ret = array();
      foreach (array_map("trim", explode(",", $to)) as $t) {
        preg_match('/<([^@]+@[^@]+)>/', $t, $matches);
        $ret[] = (isset($matches[1])) ? $matches[1] : $t;
      }
      
      return $ret;
    }
  }
  
  public function getToName()
  {
    $to = $this->getHeader("To");
    if ($to === "") return "";
    
    if (strpos($to, ",") === false) {
      return $this->removeQuote(preg_replace('/<[^@]+@[^@]+>/', "", $to));
    } else {
      $ret = array();
      foreach (array_map("trim", explode(",", $to)) as $t) {
        $ret[] = $this->removeQuote(preg_replace('/<[^@]+@[^@]+>/', "", $t));
      }
      
      return $ret;
    }
  }
  
  public function getSubject()
  {
    return $this->getHeader("Subject");
  }
  
  public function getBody()
  {
    return $this->body;
  }
  
  public function getHtml()
  {
    return $this->html;
  }
  
  protected function removeQuote($str)
  {
    if ($str{0} === '"') {
      $_tmp = trim($str);
      if ($_tmp{strlen($_tmp) - 1} === '"') {
        $str = substr($_tmp, 1, -1);
      }
    }
    
    return $str;
  }
}
