<?php

/**
 * functional test for Sabel Application
 *
 * @category   Test
 * @package    org.sabel.test
 * @author     Mori Reo <mori.reo@sabel.jp>
 * @copyright  2004-2008 Mori Reo <mori.reo@sabel.jp>
 * @license    http://www.opensource.org/licenses/bsd-license.php  BSD License
 */
class Sabel_Test_Functional extends PHPUnit_Framework_TestCase
{
  protected function request(Sabel_Request $request, $session = null, $maxRedirects = 0)
  {
    if ($session === null) {
      $session = Sabel_Session_InMemory::create();
    }
    
    Sabel_Cookie_Factory::create()->set($session->getName(), $session->getId());
    
    if ($maxRedirects > 0) {
      return $this->requestWithRedirect($request, $session, $maxRedirects);
    } else {
      $bus = new Sabel_Bus();
      $bus->set("request", $request);
      $bus->set("session", $session);
      $bus->run(new Config_Bus());
      
      return $bus->get("response");
    }
  }
  
  protected function httpGet($uri, $session = null, $maxRedirects = 0)
  {
    $request = new Sabel_Request_Object(normalize_uri($uri));
    
    if (isset($parsedUrl["query"]) && !empty($parsedUrl["query"])) {
      parse_str($parsedUrl["query"], $get);
      $request->setGetValues($get);
    }
    
    return $this->request($request, $session, $maxRedirects);
  }
  
  protected function requestWithRedirect($request, $session, $maxRedirects)
  {
    $responses = array();
    $response  = $this->request($request, $session, 0);
    $responses[] = $response;
    
    if (!$this->isRedirected($response)) {
      return $responses;
    }
    
    $location = $response->getLocation();
    for ($i = 0; $i < $maxRedirects; $i++) {
      $response = $this->httpGet($location, $session, 0);
      $responses[] = $response;
      
      if ($this->isRedirected($response)) {
        $location = $response->getLocation();
      } else {
        break;
      }
    }
    
    return $responses;
  }
  
  protected function isRedirected($response)
  {
    return $response->isRedirected();
  }
  
  protected function assertRedirect($uri, $toUri, $session = null)
  {
    $response = $this->request($uri, $session);
    
    if ($this->isRedirected($response)) {
      $this->assertEquals($toUri, $response->getLocation());
    } else {
      $this->fail("not redirected");
    }
    
    return $response;
  }
  
  public function testDummy(){}
}