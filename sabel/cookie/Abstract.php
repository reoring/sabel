<?php

/**
 * Sabel_Cookie_Abstract
 *
 * @abstract
 * @category   Cookie
 * @package    org.sabel.cookie
 * @author     Ebine Yutaka <ebine.yutaka@sabel.jp>
 * @copyright  2004-2008 Mori Reo <mori.reo@sabel.jp>
 * @license    http://www.opensource.org/licenses/bsd-license.php  BSD License
 */
abstract class Sabel_Cookie_Abstract extends Sabel_Object
{
  public function delete($key, $options = array())
  {
    $options["expire"] = time() - 3600;
    $this->set($key, "", $options);
  }
  
  protected function createOptions(array $options)
  {
    if (!isset($options["expire"]))   $options["expire"]   = time() + 86400;
    if (!isset($options["path"]))     $options["path"]     = "/";
    if (!isset($options["domain"]))   $options["domain"]   = "";
    if (!isset($options["secure"]))   $options["secure"]   = false;
    if (!isset($options["httpOnly"])) $options["httpOnly"] = false;
    
    return $options;
  }
}
