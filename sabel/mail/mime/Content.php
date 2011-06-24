<?php

/**
 * Sabel_Mail_Mime_Content
 *
 * @category  Mail
 * @package   org.sabel.mail
 * @author    Ebine Yutaka <ebine.yutaka@sabel.jp>
 * @copyright 2004-2008 Mori Reo <mori.reo@sabel.jp>
 * @license   http://www.opensource.org/licenses/bsd-license.php  BSD License
 */
class Sabel_Mail_Mime_Content extends Sabel_Object
{
  protected $type = "";
  protected $charset = "";
  protected $name = "";
  protected $boundary = "";
  protected $encoding = "";
  protected $disposition = "";
  
  public function setType($type)
  {
    $this->type = $type;
  }
  
  public function getType()
  {
    return $this->type;
  }
  
  public function setCharset($charset)
  {
    $this->charset = $charset;
  }
  
  public function getCharset()
  {
    return $this->charset;
  }
  
  public function setName($name)
  {
    $this->name = $name;
  }
  
  public function getName()
  {
    return $this->name;
  }
  
  public function setBoundary($boundary)
  {
    $this->boundary = $boundary;
  }
  
  public function getBoundary()
  {
    return $this->boundary;
  }
  
  public function setEncoding($encoding)
  {
    $this->encoding = strtolower($encoding);
  }
  
  public function getEncoding()
  {
    return $this->encoding;
  }
  
  public function setDisposition($disposition)
  {
    $this->disposition = strtolower($disposition);
  }
  
  public function getDisposition()
  {
    return $this->disposition;
  }
}
