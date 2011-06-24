<?php

function add_include_path($path)
{
  set_include_path(get_include_path() . PATH_SEPARATOR . $path);
}

function unshift_include_path($path)
{
  set_include_path($path . PATH_SEPARATOR . get_include_path());
}

function unshift_include_paths($paths, $prefix = "")
{
  $path = "";
  foreach ($paths as $p) {
    $path .= $prefix . $p . PATH_SEPARATOR;
  }
  
  set_include_path($path . get_include_path());
}

if (extension_loaded("mbstring")) {
  function htmlescape($str, $charset = null)
  {
    if (defined("APP_ENCODING")) {
      $charset = APP_ENCODING;
    } elseif (empty($charset)) {
      $charset = mb_internal_encoding();
    }
    
    return htmlentities($str, ENT_QUOTES, $charset);
  }
  
  function xmlescape($str, $charset = null)
  {
    if (defined("APP_ENCODING")) {
      $charset = APP_ENCODING;
    } elseif (empty($charset)) {
      $charset = mb_internal_encoding();
    }
    
    return str_replace("&#039;", "&apos;", htmlspecialchars($str, ENT_QUOTES, $charset));
  }
} else {
  function htmlescape($str, $charset = null)
  {
    return htmlentities($str, ENT_QUOTES);
  }
  
  function xmlescape($str, $charset = null) {
    return str_replace("&#039;", "&apos;", htmlspecialchars($str, ENT_QUOTES));
  }
}

function remove_nullbyte($arg)
{
  if (is_string($arg)) {
    return str_replace("\000", "", $arg);
  } elseif (is_array($arg)) {
    foreach ($arg as &$v) {
      if (is_string($v)) {
        $v = str_replace("\000", "", $v);
      }
    }
    
    return $arg;
  } else {
    return $arg;
  }
}

function get_temp_dir()
{
  static $exists = null;
  
  if ($exists === null) {
    $exists = function_exists("sys_get_temp_dir");
  }
  
  if ($exists) {
    return sys_get_temp_dir();
  } elseif (isset($_ENV["TMP"])) {
    return realpath($_ENV["TMP"]);
  } elseif (isset($_ENV["TMPDIR"])) {
    return realpath($_ENV["TMPDIR"]);
  } elseif (isset($_ENV["TEMP"])) {
    return realpath($_ENV["TEMP"]);
  } else {
    if ($tmpFile = tempnam(md5hash(), "sbl_")) {
      $dirName = realpath(dirname($tmpFile));
      unlink($tmpFile);
      return $dirName;
    } else {
      return false;
    }
  }
}

function get_mime_type($path /* or filedata */)
{
  $tmpFile = null;
  
  if (!is_file($path)) {
    if ($tmpDir = get_temp_dir()) {
      $tmpFile = $tmpDir . DS . md5hash();
      if (file_put_contents($tmpFile, $path)) {
        $path = $tmpFile;
      } else {
        return false;
      }
    } else {
      return false;
    }
  }
  
  $ret = false;
  
  if (!is_file($path)) {
    return $ret;
  } elseif (extension_loaded("fileinfo")) {
    if (defined("FILEINFO_MAGICDB")) {
      $finfo = new finfo(FILEINFO_MIME, FILEINFO_MAGICDB);
    } else {
      $finfo = new finfo(FILEINFO_MIME);
    }
    
    $ret = $finfo->file($path);
  } elseif (DS === "/") {  // *nix
    $ret = trim(shell_exec("file -ib " . escapeshellcmd($path)));
  } elseif (function_exists("mime_content_type")) {
    $ret = mime_content_type($path);
  }
  
  if ($tmpFile !== null) {
    unlink($tmpFile);
  }
  
  return $ret;
}

if (!function_exists("lcfirst")) {
  function lcfirst($str)
  {
    if (!is_string($str) || $str === "") {
      return "";
    } else {
      $str{0} = strtolower($str{0});
      return $str;
    }
  }
}

function now()
{
  return date("Y-m-d H:i:s");
}

function md5hash()
{
  return md5(uniqid(mt_rand(), true));
}

function sha1hash()
{
  return sha1(uniqid(mt_rand(), true));
}

function load()
{
  static $container = null;
  
  if ($container === null) {
    $container = Sabel_Container::create();
  }
  
  $args = func_get_args();
  
  return call_user_func_array(array($container, "load"), $args);
}

function l($message, $level = SBL_LOG_INFO, $identifier = "default")
{
  Sabel_Logger::create()->write($message, $level, $identifier);
}

function normalize_uri($uri)
{
  $uri = trim(preg_replace("@/{2,}@", "/", $uri), "/");
  $parsedUrl = parse_url("http://localhost/{$uri}");
  return ltrim($parsedUrl["path"], "/");
}

function is_empty($value)
{
  return ($value === null || $value === "" || $value === array() || $value === false);
}

function array_isset($key, $array)
{
  if (($count = preg_match_all('/\[(.+)\]/U', $key, $matches)) > 0) {
    $key1 = substr($key, 0, strpos($key, "["));
    
    if (array_isset($key1, $array)) {
      $array = $array[$key1];
      foreach ($matches[1] as $_key) {
        if (array_isset($_key, $array)) {
          $array = $array[$_key];
        } else {
          return false;
        }
      }
      
      return true;
    } else {
      return false;
    }
  } else {
    return (isset($array[$key]) && !is_empty($array[$key]));
  }
}

function realempty($value)
{
  return is_empty($value);
}

function dump()
{
  if (is_cli()) {
    echo PHP_EOL;
    echo "================================================" . PHP_EOL;
  } else {
    echo '<pre style="background: #FFF; color: #333; ' .
         'border: 1px solid #ccc; margin: 5px; padding: 5px;">';
  }
  
  foreach (func_get_args() as $value) {
    var_dump($value);
  }
  
  if (is_cli()) {
    echo "================================================" . PHP_EOL;
  } else {
    echo '</pre>';
  }
}

function get_server_name()
{
  if (defined("SERVICE_DOMAIN")) {
    return SERVICE_DOMAIN;
  } elseif (isset($_SERVER["SERVER_NAME"])) {
    return $_SERVER["SERVER_NAME"];
  } elseif (isset($_SERVER["HTTP_HOST"])) {
    return $_SERVER["HTTP_HOST"];
  } else {
    return "localhost";
  }
}

function is_cli()
{
  return (PHP_SAPI === "cli");
}

function is_ipaddr($arg)
{
  if (is_string($arg)) {
    $ptn = "(0|[1-9][0-9]{0,2})";
    if (preg_match("/^{$ptn}\.{$ptn}\.{$ptn}\.{$ptn}$/", $arg) === 1) {
      foreach (explode(".", $arg) as $part) {
        if ($part > 255) return false;
      }
      
      return true;
    } else {
      return false;
    }
  } else {
    return false;
  }
}

function is_number($num)
{
  if (is_int($num)) {
    return true;
  } elseif (is_string($num)) {
    return ($num === "0" || preg_match('/^\-?[1-9][0-9]*$/', $num) === 1);
  } else {
    return false;
  }
}

function is_natural_number($num)
{
  if (is_int($num)) {
    return ($num >= 0);
  } elseif (is_string($num)) {
    return ($num === "0" || preg_match('/^[1-9][0-9]*$/', $num) === 1);
  } else {
    return false;
  }
}

function strtoint($str)
{
  if (is_int($str)) {
    return $str;
  } elseif (!is_string($str) || is_empty($str)) {
    return 0;
  }
  
  $len  = strlen($str);
  $char = strtolower($str{$len - 1});
  
  if (in_array($char, array("k", "m", "g"), true)) {
    $num = substr($str, 0, $len - 1);
    if (is_number($num)) {
      switch ($char) {
        case "k": return $num * 1024;
        case "m": return $num * pow(1024, 2);
        case "g": return $num * pow(1024, 3);
        default : return 0;
      }
    } else {
      return 0;
    }
  } else {
    return (is_number($str)) ? (int)$str : 0;
  }
}
