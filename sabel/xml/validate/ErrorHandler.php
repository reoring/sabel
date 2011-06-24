<?php

/**
 * Sabel_Xml_Validate_ErrorHandler
 *
 * @category   XML
 * @package    org.sabel.xml
 * @author     Ebine Yutaka <ebine.yutaka@sabel.jp>
 * @copyright  2004-2008 Mori Reo <mori.reo@sabel.jp>
 * @license    http://www.opensource.org/licenses/bsd-license.php  BSD License
 */
class Sabel_Xml_Validate_ErrorHandler extends Sabel_Object
{
  protected $errors = array();
  
  public function setError($errno, $errstr)
  {
    $this->errors[] = $errstr;
  }
  
  public function hasError()
  {
    return !empty($this->errors);
  }
  
  public function getErrors()
  {
    return $this->errors;
  }
}
