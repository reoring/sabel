<?php

/**
 * Sabel_View_Renderer
 *
 * @category   Template
 * @package    org.sabel.template
 * @author     Mori Reo <mori.reo@sabel.jp>
 * @author     Ebine Yutaka <mori.reo@sabel.jp>
 * @copyright  2004-2008 Mori Reo <mori.reo@sabel.jp>
 * @license    http://www.opensource.org/licenses/bsd-license.php  BSD License
 */
class Sabel_View_Renderer extends Sabel_Object
{
  public function rendering($_tpl_contents, $_tpl_values, $_tpl_path = null)
  {
    if ($_tpl_path === null || !is_file($_tpl_path)) {
      $hash = $this->createHash($_tpl_contents);
      $_tpl_path = COMPILE_DIR_PATH . DS . $hash;
      file_put_contents($_tpl_path, $_tpl_contents);
    }
    
    extract($_tpl_values, EXTR_OVERWRITE);
    ob_start();
    include ($_tpl_path);
    return ob_get_clean();
  }
  
  public function partial($name, $assign = array())
  {
    $bus  = Sabel_Context::getContext()->getBus();
    $view = $bus->get("view");
    
    if (($template = $view->getValidLocation($name)) !== null) {
      $responses = array_merge($bus->get("response")->getResponses(), $assign);
      $contents  = $template->getContents();
      return $this->rendering($contents, $responses, $template->getPath());
    } else {
      throw new Sabel_Exception_Runtime("template is not found.");
    }
  }
  
  protected function createHash($template)
  {
    return md5($template);
  }
}
