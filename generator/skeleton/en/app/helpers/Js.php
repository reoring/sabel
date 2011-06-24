<?php

/**
 * Helpers_Js
 *
 * @category   Helper
 * @package    org.sabel.helper
 * @author     Hamanaka Kazuhiro <hamanaka.kazuhiro@sabel.jp>
 * @copyright  2004-2008 Mori Reo <mori.reo@sabel.jp>
 * @license    http://www.opensource.org/licenses/bsd-license.php BSD License
 */
class Helpers_Js
{
  public static function effectUpdater($sourceId, $replaceId, $effect = "Slide")
  {
    $include = <<<INC
<script type="text/javascript" src="%s" charset="UTF-8"></script>
INC;
    
    $script = <<<JS
<script type="text/javascript">
new Sabel.Event(window, "load", function() {
  var updater = new Sabel.PHP.EffectUpdater("%s", "%s");

  Sabel.get("%s").observe("click", function(evt) {
    updater.fire(this.href);
    Sabel.Event.preventDefault(evt);
  });
});
</script>
JS;
    
    $buf   = array();
    $buf[] = sprintf($include, linkto("js/helpers/EffectUpdater.js"));
    $buf[] = sprintf($script, $replaceId, $effect, $sourceId);
    
    return join($buf, "\n");
  }

  public static function ajaxPager($replaceId, $pagerClass = "sbl_pager")
  {
    $include = <<<INC
<script type="text/javascript" src="%s" charset="UTF-8"></script>
INC;
    
    $buf   = array();
    $buf[] = sprintf($include, linkto("js/helpers/EffectUpdater.js"));
    $buf[] = sprintf($include, linkto("js/helpers/AjaxPager.js"));
    $buf[] = "\n";
    $buf[] = '<script type="text/javascript">';
    $buf[] = sprintf('new Sabel.Event(window, "load", function() { new Sabel.PHP.AjaxPager("%s", "%s"); });', $replaceId, $pagerClass);
    $buf[] = '</script>';
    
    return join($buf, "") . "\n";
  }

  /* @todo
  public static function formValidator($formObj, $errBox = "sbl_errmsg")
  {
    $include = <<<INC
<script type="text/javascript" src="%s" charset="UTF-8"></script>
INC;
    
    $model   = $formObj->getModel();
    $columns = $model->getColumns();
    $errMsgs = Sabel_Db_Validate_Config::getMessages();
    $lNames  = Sabel_Db_Model_Localize::getColumnNames($model->getName());
    
    $data = array("data" => array(), "errors" => $errMsgs);
    foreach ($columns as $c) {
      $name = $c->name;
      if (isset($lNames[$c->name])) {
        $c->name = $lNames[$c->name];
      }
      
      $data["data"][$name] = array_change_key_case((array) $c, CASE_UPPER);
    }
    
    $buf   = array();
    $buf[] = sprintf($include, linkto("js/helpers/FormValidator.js"));
    $buf[] = "\n";
    $buf[] = '<script type="text/javascript">';
    $buf[] = 'new Sabel.PHP.FormValidator(' . json_encode($data) . ');';
    $buf[] = '</script>';
    
    return join($buf, "") . "\n";
  }
  */
}
