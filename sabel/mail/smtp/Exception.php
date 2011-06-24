<?php

/**
 * Sabel_Mail_Smtp_Exception
 *
 * @category   Mail
 * @package    org.sabel.mail
 * @author     Ebine Yutaka <ebine.yutaka@sabel.jp>
 * @copyright  2004-2008 Mori Reo <mori.reo@sabel.jp>
 * @license    http://www.opensource.org/licenses/bsd-license.php  BSD License
 */
class Sabel_Mail_Smtp_Exception extends Sabel_Exception_Runtime
{
  protected $responseCode = null;
  
  public function setResponseCode($code)
  {
    $this->responseCode = $code;
  }
  
  public function getResponseCode()
  {
    return $this->responseCode;
  }
}
