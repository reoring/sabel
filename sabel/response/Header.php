<?php

/**
 * Sabel_Response_Header
 *
 * @category   Response
 * @package    org.sabel.response
 * @author     Ebine Yutaka <ebine.yutaka@sabel.jp>
 * @copyright  2004-2008 Mori Reo <mori.reo@sabel.jp>
 * @license    http://www.opensource.org/licenses/bsd-license.php  BSD License
 */
class Sabel_Response_Header
{
  protected static $redirectCode = array(300, 301, 302, 303, 305, 307);
  
  public static function output(Sabel_Response $response)
  {
    $headers = self::createHeaders($response);
    if (!$headers) return array();
    
    if (strtolower(PHP_SAPI) !== "cli") {
      array_map("header", $headers);
      
      if (in_array($response->getStatus()->getCode(), self::$redirectCode, true)) {
        l("redirect: " . $response->getLocation());
      }
    }
    
    return $headers;
  }
  
  protected static function createHeaders(Sabel_Response $response)
  {
    $headers = array();
    $status  = $response->getStatus();
    
    $httpVersion = "HTTP/" . $response->getHttpVersion();
    $headers[] = $httpVersion . " " . $status->toString();
    
    if ($response->hasHeaders()) {
      foreach ($response->getHeaders() as $message => $value) {
        $headers[] = ucfirst($message) . ": " . $value;
      }
    }
    
    if (in_array($status->getCode(), self::$redirectCode, true)) {
      $headers[] = "Location: " . $response->getLocation();
    }
    
    return $headers;
  }
}
