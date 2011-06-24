<?php

/**
 * Sabel_Mail_Sender_PHP
 *
 * @category   Mail
 * @package    org.sabel.mail
 * @author     Ebine Yutaka <ebine.yutaka@sabel.jp>
 * @copyright  2004-2008 Mori Reo <mori.reo@sabel.jp>
 * @license    http://www.opensource.org/licenses/bsd-license.php  BSD License
 */
class Sabel_Mail_Sender_PHP
  extends Sabel_Object implements Sabel_Mail_Sender_Interface
{
  public function send(array $headers, $body, $options = array())
  {
    $recipients  = $this->getRecipients($headers);
    $subject     = $this->getSubject($headers);
    $headersText = implode(Sabel_Mail::getEol(), $this->createHeaderText($headers));
    
    if (isset($options["parameters"])) {
      return mail($recipients, $subject, $body, $headersText, $options["parameters"]);
    } else {
      return mail($recipients, $subject, $body, $headersText);
    }
  }
  
  protected function createHeaderText($headersArray)
  {
    $headers = array();
    
    foreach ($headersArray as $name => $header) {
      if ($name === "From") {
        if ($header["name"] === "") {
          $headers[] = "From: <{$header["address"]}>";
        } else {
          $headers[] = "From: {$header["name"]} <{$header["address"]}>";
        }
      } elseif ($name === "Cc") {
        foreach ($header as $value) {
          if ($value["name"] === "") {
            $headers[] = "Cc: <{$value["address"]}>";
          } else {
            $headers[] = "Cc: {$value["name"]} <{$value["address"]}>";
          }
        }
      } elseif (is_array($header)) {
        foreach ($header as $value) {
          $headers[] = $name . ": " . $value;
        }
      } else {
        $headers[] = $name . ": " . $header;
      }
    }
    
    return $headers;
  }
  
  protected function getRecipients(&$headers)
  {
    $recipients = array();
    if (isset($headers["To"])) {
      foreach ($headers["To"] as $recipient) {
        if ($recipient["name"] === "") {
          $recipients[] = $recipient["address"];
        } else {
          $recipients[] = $recipient["name"] . " <{$recipient["address"]}>";
        }
      }
      
      unset($headers["To"]);
      return implode(", ", $recipients);
    } else {
      $message = __METHOD__ . "() empty recipients.";
      throw new Sabel_Mail_Exception($message);
    }
  }
  
  protected function getSubject(&$headers)
  {
    $subject = "";
    if (isset($headers["Subject"])) {
      $subject = $headers["Subject"];
      unset($headers["Subject"]);
    }
    
    return $subject;
  }
}
