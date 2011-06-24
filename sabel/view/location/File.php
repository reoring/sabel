<?php

/**
 * Sabel_View_Location_File
 *
 * @category   View
 * @package    org.sabel.view
 * @author     Ebine Yutaka <ebine.yutaka@sabel.jp>
 * @copyright  2004-2008 Mori Reo <mori.reo@sabel.jp>
 * @license    http://www.opensource.org/licenses/bsd-license.php  BSD License
 */
class Sabel_View_Location_File extends Sabel_View_Location
{
  public function getContents()
  {
    return file_get_contents($this->getPath());
  }
  
  public function create($contents = "")
  {
    file_put_contents($this->getPath(), $contents);
  }
  
  public function delete()
  {
    unlink($this->getPath());
  }
  
  public function isValid()
  {
    $path = $this->getPath();
    return (is_file($path) && is_readable($path));
  }
}
