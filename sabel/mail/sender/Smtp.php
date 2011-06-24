<?php

/**
 * Sabel_Mail_Sender_Smtp
 *
 * @category   Mail
 * @package    org.sabel.mail
 * @author     Ebine Yutaka <ebine.yutaka@sabel.jp>
 * @copyright  2004-2008 Mori Reo <mori.reo@sabel.jp>
 * @license    http://www.opensource.org/licenses/bsd-license.php  BSD License
 */
class Sabel_Mail_Sender_Smtp
  extends Sabel_Object implements Sabel_Mail_Sender_Interface
{
  const DEFAULT_TIMEOUT = 5;
  
  /**
   * @var array
   */
  protected $config = array();
  
  /**
   * @var resource
   */
  protected $smtp = null;
  
  /**
   * @var string
   */
  protected $eol = "\r\n";
  
  public function __construct(array $config = array())
  {
    if (!isset($config["host"])) {
      $config["host"] = "localhost";
    }
    
    if (isset($config["eol"])) {
      $this->eol = $config["eol"];
    }
    
    if (!isset($config["timeout"])) {
      $config["timeout"] = self::DEFAULT_TIMEOUT;
    }
    
    $this->config = $config;
    $this->connect();
  }
  
  public function disconnect()
  {
    if (is_resource($this->smtp)) {
      fclose($this->smtp);
    }
  }
  
  public function send(array $headers, $body, $options = array())
  {
    $this->command("MAIL FROM:<{$headers["From"]["address"]}>", "250");
    $this->sendRcptTo($headers);
    $this->command("DATA", "354");
    $this->sendHeaders($headers);
    $this->command($this->eol . $body . $this->eol);
    $this->command(".", "250");
  }
  
  protected function connect()
  {
    $server = $this->config["host"];
    
    if (isset($this->config["port"])) {
      $port = $this->config["port"];
    } else {
      $_tmp = strtolower(substr($server, 0, 6));
      $port = ($_tmp === "ssl://" || $_tmp === "tls://") ? "465" : "25";
    }
    
    $smtp = fsockopen($server, $port, $errno, $errstr, $this->config["timeout"]);
    
    if ($smtp === false || strpos(rtrim(fgets($smtp)), "220") !== 0) {
      $message = __METHOD__ . "()" ." can't connect to the SMTP Server. "
               . "HOST => '{$server}, PORT => '{$port}' / reason: {$errno}/{$errstr}";
      
      throw new Sabel_Mail_Smtp_Exception_ConnectionRefused($message);
    }
    
    $this->smtp = $smtp;
    $this->command("EHLO {$server}");
    
    while ($result = trim(fgets($this->smtp))) {
      if (strpos($result, "250 ") === 0) break;
      
      if (strpos($result, "250") !== 0) {
        preg_match("/^[0-9]+/", $result, $matches);
        $message = "got unexpected response code '{$matches[0]}'.";
        $exception = new Sabel_Mail_Smtp_Exception($message);
        $exception->setResponseCode($matches[0]);
        throw $exception;
      }
    }
    
    if (isset($this->config["auth"])) {
      try {
        $this->_auth($this->config["auth"]);
      } catch (Sabel_Mail_Smtp_Exception $e) {
        $exception = new Sabel_Mail_Smtp_Exception_AuthFailure($e->getMessage());
        $exception->setResponseCode($e->getResponseCode());
        throw $exception;
      }
    }
  }
  
  protected function sendRcptTo($headers)
  {
    if (empty($headers["To"])) {
      $message = __METHOD__ . "() empty recipients.";
      throw new Sabel_Mail_Exception($message);
    }
    
    try {
      foreach ($headers["To"] as $rcpt) {
        $this->command("RCPT TO:<{$rcpt["address"]}>", "250");
      }
      
      if (isset($headers["Cc"])) {
        foreach ($headers["Cc"] as $rcpt) {
          $this->command("RCPT TO:<{$rcpt["address"]}>", "250");
        }
      }
      
      if (isset($headers["Bcc"])) {
        foreach ($headers["Bcc"] as $rcpt) {
          $this->command("RCPT TO:<{$rcpt}>", "250");
        }
      }
    } catch (Sabel_Mail_Smtp_Exception $e) {
      $exception = new Sabel_Mail_Smtp_Exception_RecipientRefused($e->getMessage());
      $exception->setResponseCode($e->getResponseCode());
      throw $exception;
    }
  }
  
  protected function sendHeaders($headers)
  {
    foreach ($headers as $name => $header) {
      if ($name === "From") {
        if ($header["name"] === "") {
          $this->command("From: {$header["address"]}");
        } else {
          $this->command("From: {$header["name"]} <{$header["address"]}>");
        }
      } elseif ($name === "To" || $name === "Cc") {
        $value = array();
        foreach ($header as $rcpt) {
          if ($rcpt["name"] === "") {
            $value[] = $rcpt["address"];
          } else {
            $value[] = "{$rcpt["name"]} <{$rcpt["address"]}>";
          }
        }
        
        $this->command($name . ": " . implode(", ", $value));
      } elseif (is_array($header)) {
        foreach ($header as $value) {
          $this->command("{$name}: {$value}");
        }
      } else {
        $this->command("{$name}: {$header}");
      }
    }
  }
  
  protected function plainAuth($user, $password)
  {
    $command = "AUTH PLAIN " . base64_encode("{$user}\000{$user}\000{$password}");
    $this->command($command, "235");
  }
  
  protected function loginAuth($user, $password)
  {
    $this->command("AUTH LOGIN", "334");
    $this->command(base64_encode($user), "334");
    $this->command(base64_encode($password), "235");
  }
  
  protected function crammd5Auth($user, $password)
  {
    $result = $this->command("AUTH CRAM-MD5", "334");
    $challenge = base64_decode($result);
    
    if (strlen($password) > 64) {
      $password = pack("H*", md5($password));
    } elseif (strlen($password) < 64) {
      $password = str_pad($password, 64, "\000");
    }
    
    $k_ipad = substr($password, 0, 64) ^ str_repeat("\066", 64);
    $k_opad = substr($password, 0, 64) ^ str_repeat("\134", 64);
    $digest = md5($k_opad . pack("H*", md5($k_ipad . $challenge)));
    
    $this->command(base64_encode($user . " " . $digest), "235");
  }
  
  protected function command($command, $expectedStatus = null)
  {
    fwrite($this->smtp, $command . $this->eol);
    
    if ($expectedStatus === null) {
      return true;
    } else {
      $result = rtrim(fgets($this->smtp));
      preg_match("/^[0-9]+/", $result, $matches);
      
      if ($matches[0] === $expectedStatus) {
        return substr($result, strlen($matches[0]) + 1);
      } else {
        $message = "got unexpected response code '{$matches[0]}'.";
        $exception = new Sabel_Mail_Smtp_Exception($message);
        $exception->setResponseCode($matches[0]);
        throw $exception;
      }
    }
  }
  
  protected function _auth($authMethod)
  {
    $method = strtolower($authMethod) . "Auth";
    if ($this->hasMethod($method)) {
      $this->$method($this->config["user"], $this->config["password"]);
    } else {
      $message = __METHOD__ . "() {$authMethod} is unsupported Authentication method.";
      throw new Sabel_Mail_Exception($message);
    }
  }
}
