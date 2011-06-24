<?php

/**
 * Sabel_Request
 *
 * @interface
 * @category   Request
 * @package    org.sabel.request
 * @author     Mori Reo <mori.reo@sabel.jp>
 * @copyright  2004-2008 Mori Reo <mori.reo@sabel.jp>
 * @license    http://www.opensource.org/licenses/bsd-license.php  BSD License
 */
interface Sabel_Request
{
  const GET    = "GET";
  const POST   = "POST";
  const PUT    = "PUT";
  const DELETE = "DELETE";
  
  public function setUri($uri);
  public function getUri();
  
  public function get($uri);
  public function post($uri);
  public function put($uri);
  public function delete($uri);
  
  public function isGet();
  public function isPost();
  public function isPut();
  public function isDelete();
  
  public function setGetValue($name, $value);
  public function setGetValues(array $values);
  public function fetchGetValue($name);
  public function fetchGetValues();
  
  public function setPostValue($name, $value);
  public function setPostValues(array $values);
  public function fetchPostValue($name);
  public function fetchPostValues();
  
  public function setParameterValue($name, $value);
  public function setParameterValues(array $values);
  public function fetchParameterValue($name);
  public function fetchParameterValues();
  
  public function setFile($name, $file);
  public function setFiles(array $files);
  public function getFile($name);
  public function getFiles();
  
  public function setHttpHeaders(array $headers);
  public function getHttpHeader($name);
  public function getHttpHeaders();
}
