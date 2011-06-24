<?php

/**
 * Sabel_View_Object
 *
 * @category   View
 * @package    org.sabel.view
 * @author     Ebine Yutaka <ebine.yutaka@sabel.jp>
 * @copyright  2004-2008 Mori Reo <mori.reo@sabel.jp>
 * @license    http://www.opensource.org/licenses/bsd-license.php  BSD License
 */
class Sabel_View_Object extends Sabel_Object implements Sabel_View
{
  /**
   * @var Sabel_View_Renderer
   */
  protected $renderer = null;
  
  /**
   * @var Sabel_View_Location[]
   */
  protected $locations = array();
  
  /**
   * @var string
   */
  protected $tplName = "";
  
  public function __construct($name, Sabel_View_Location $location)
  {
    $this->addLocation($name, $location);
  }
  
  public function addLocation($name, Sabel_View_Location $location)
  {
    if (is_string($name) && $name !== "") {
      $this->locations[$name] = $location;
    } else {
      $message = "argument(1) must be a string.";
      throw new Sabel_Exception_Runtime($message);
    }
  }
  
  public function getLocation($name)
  {
    if (isset($this->locations[$name])) {
      return $this->locations[$name];
    } else {
      return null;
    }
  }
  
  public function getLocations()
  {
    return $this->locations;
  }
  
  public function setName($tplName)
  {
    $this->tplName = $tplName;
  }
  
  public function getName()
  {
    return $this->tplName;
  }
  
  public function setRenderer(Sabel_View_Renderer $renderer)
  {
    $this->renderer = $renderer;
  }
  
  public function getRenderer()
  {
    return $this->renderer;
  }
  
  public function rendering(Sabel_View_Location $location, array $assigns = array())
  {
    return $this->renderer->rendering($location->getContents(),
                                      $assigns,
                                      $location->getPath());
  }
  
  public function getValidLocation($tplPath = null)
  {
    if ($tplPath === null && $this->tplName === "") {
      throw new Sabel_Exception_Runtime("template name is null.");
    } else {
      if ($tplPath === null) $tplPath = $this->tplName;
      foreach ($this->locations as $location) {
        $location->name($tplPath);
        if ($location->isValid()) return $location;
      }
    }
    
    return null;
  }
  
  public function getContents($tplPath = null)
  {
    $location = $this->getValidLocation($tplPath);
    return ($location !== null) ? $location->getContents() : "";
  }
  
  public function create($name, $tplPath, $contents = "")
  {
    if ($location = $this->getLocation($name)) {
      $location->name($tplPath);
      $location->create($contents);
    } else {
      throw new Sabel_Exception_Runtime("such a location name is not registered.");
    }
  }
  
  public function delete($name, $tplPath)
  {
    if ($location = $this->getLocation($name)) {
      $location->name($tplPath);
      $location->delete();
    } else {
      throw new Sabel_Exception_Runtime("such a location name is not registered.");
    }
  }
  
  public function isValid($name, $tplPath)
  {
    if ($location = $this->getLocation($name)) {
      $location->name($tplPath);
      return $location->isValid();
    } else {
      throw new Sabel_Exception_Runtime("such a location name is not registered.");
    }
  }
}
