<?php

/**
 * Sabel_Mail_Sender_Interface
 *
 * @interface
 * @category   Mail
 * @package    org.sabel.mail
 * @author     Ebine Yutaka <ebine.yutaka@sabel.jp>
 * @copyright  2004-2008 Mori Reo <mori.reo@sabel.jp>
 * @license    http://www.opensource.org/licenses/bsd-license.php  BSD License
 */
interface Sabel_Mail_Sender_Interface
{
  public function send(array $headers, $body, $options = array());
}
