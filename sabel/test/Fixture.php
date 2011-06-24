<?php

/**
 * Sabel_Test_Fixture
 *
 * @category   Test
 * @package    org.sabel.test
 * @author     Ebine Yutaka <yutaka@ebine.org>
 * @copyright  2004-2008 Mori Reo <mori.reo@sabel.jp>
 * @license    http://www.opensource.org/licenses/bsd-license.php  BSD License
 */
class Sabel_Test_Fixture extends Sabel_Object
{
  protected $model = null;
  protected $modelName = "";
  
  public function __construct()
  {
    if ($this->model !== false) {
      if ($this->modelName === "") {
        $this->modelName = substr($this->getName(), 8);
      }
      
      $this->model = MODEL($this->modelName);
    }
  }
  
  public function initialize()
  {
    
  }
  
  protected function insert($data)
  {
    return $this->model->insert($data);
  }
  
  protected function deleteAll()
  {
    $this->model->delete();
  }
}
