<?php

/**
 * Install
 *
 * @category   Sakle
 * @package    org.sabel.sakle
 * @author     Ebine Yutaka <yutaka@ebine.org>
 * @copyright  2004-2008 Mori Reo <mori.reo@sabel.jp>
 * @license    http://www.opensource.org/licenses/bsd-license.php  BSD License
 */
class Install extends Sabel_Sakle_Task
{
  protected $repo = "http://sabel.php-framework.org";
  
  public function initialize()
  {
    $this->fs = new Sabel_Util_FileSystem(RUN_BASE);
  }
  
  public function run()
  {
    $args = $this->arguments;
    
    if (Sabel_Console::hasOption("v", $args)) {
      $version = Sabel_Console::getOption("v", $args);
    } else {
      $version = "HEAD";
    }
    
    if (Sabel_Console::hasOption("a", $args)) {
      $this->installAddon(Sabel_Console::getOption("a", $args), $version);
    } else {
      $message = __METHOD__ . "() invalid install option.";
      throw new Sabel_Exception_Runtime($message);
    }
  }
  
  protected function installAddon($addon, $version)
  {
    $addon = lcfirst($addon);
    
    $url = "{$this->repo}/addons/{$addon}/{$version}";
    
    try {
      $client = new Sabel_Http_Client($url);
      $this->_installAddon($addon, $client->request()->getContent());
    } catch (Exception $e) {
      $this->warning($e->getMessage());
    }
  }
  
  protected function _installAddon($name, $response)
  {
    if (substr($response, 0, 5) === "HEAD:") {
      return $this->installAddon($name, trim(substr($response, 5)));
    }
    
    $addon = Sabel_Xml_Document::create()->loadXML($response);
    
    if ($addon->tagName !== "addon") {
      $this->error("addon '{$name}' not found.");
      exit;
    }
    
    $name = $addon->at("name");
    $version = $addon->at("version");
    
    $files = $this->getAddonsFiles($addon);
    
    if (!isset($files["Addon.php"])) {
      $message = __METHOD__ . "() invalid addon. Addon.php not exists.";
    }
    
    $addonClass = ucfirst($name) . "_Addon";
    
    if (class_exists($addonClass, true)) {
      $v = constant($addonClass . "::VERSION");
      if ($v === (float)$version) {
        $this->message("{$name}_{$version} already installed.");
        return;
      } elseif ((float)$version > $v) {
        $_v = (strpos($v, ".") === false) ? "{$v}.0" : $v;
        $this->message("upgrade {$name} from {$_v} => {$version}.");
      } else {
        $this->message("nothing to install.");
        return;
      }
    }
    
    $fs = new Sabel_Util_FileSystem(RUN_BASE);
    
    foreach ($files as $path => $file) {
      $path = str_replace("/", DS, $path);
      
      if ($file["backup"] && $fs->isFile($path)) {
        $oldFile = $path . ".old";
        $fs->getFile($path)->copyTo(RUN_BASE . DS . $oldFile);
        $this->message("{$path} saved as {$oldFile}");
      }
      
      if (!$fs->isFile($path)) {
        $fs->mkfile($path)->write($file["source"])->save();
      } else {
        $fs->getFile($path)->write($file["source"])->save();
      }
      
      $this->success($path);
    }
    
    $this->success("install ok: {$name}_{$version}");
  }
  
  protected function getAddonsFiles($dom)
  {
    $ret = array();
    $files = $dom->files->item(0);
    $attrs = $dom->getAttributes()->toArray();
    
    $baseurl = $this->replaceAttributes($files->at("baseurl"), $attrs);
    
    foreach ($files->file as $file) {
      $path = $this->replaceAttributes($file->at("path"), $attrs);
      $fileUrl = $this->repo . "/" . $baseurl . "/" . $file->at("url");
      $ret[$path] = array("source" => file_get_contents($fileUrl), "backup" => false);
    }
    
    $backup = $dom->backup;
    if ($backup->length > 0) {
      foreach ($backup->file as $file) {
        $path = $this->replaceAttributes($file->at("path"), $attrs);
        if (isset($ret[$path])) {
          $ret[$path]["backup"] = true;
        }
      }
    }
    
    return $ret;
  }
  
  protected function replaceAttributes($str, $attrs)
  {
    if ($attrs) {
      foreach ($attrs as $key => $value) {
        $str = str_replace("{{$key}}", $value, $str);
      }
    }
    
    return $str;
  }
}
