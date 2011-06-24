<?php

/**
 * Sabel_View_Location
 *
 * @abstract
 * @category   View
 * @package    org.sabel.view
 * @author     Ebine Yutaka <ebine.yutaka@sabel.jp>
 * @copyright  2004-2008 Mori Reo <mori.reo@sabel.jp>
 * @license    http://www.opensource.org/licenses/bsd-license.php  BSD License
 */
abstract class Sabel_View_Location extends Sabel_Object
{
  protected $viewDirPath = "";
  protected $name        = "";
  
  abstract public function isValid();
  abstract public function getContents();
  abstract public function create($body = "");
  abstract public function delete();
  
  public function __construct($viewDirPath)
  {
    $this->viewDirPath = $viewDirPath;
  }
  
  public function name($name = null)
  {
    if ($name === null) {
      return $this->name;
    } elseif (is_string($name)) {
      $this->name = $name . TPL_SUFFIX;
    } else {
      $message = "argument must be a string.";
      throw new Sabel_Exception_InvalidArgument($message);
    }
  }
  
  public function getPath()
  {
    return MODULES_DIR_PATH . DS . $this->viewDirPath . $this->name;
  }
}
