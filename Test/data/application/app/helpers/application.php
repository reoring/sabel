<?php

function a($uri, $anchor, $uriQuery = "")
{
  if ($uriQuery === "") {
    return sprintf('<a href="%s">%s</a>', uri($uri), $anchor);
  } else {
    return sprintf('<a href="%s?%s">%s</a>', uri($uri), $uriQuery, $anchor);
  }
}

function ah($param, $anchor, $uriQuery = "")
{
  return a($param, h($anchor), $uriQuery);
}

function linkto($file)
{
  if ($bus = Sabel_Context::getContext()->getBus()) {
    if ($bus->get("NO_VIRTUAL_HOST")) {
      return dirname($_SERVER["SCRIPT_NAME"]) . "/" . $file;
    }
  }
  
  return "/" . $file;
}

function h($string, $charset = null)
{
  return htmlescape($string, $charset);
}

function mb_trim($string)
{
  $string = new Sabel_Util_String($string);
  return $string->trim()->toString();
}

function to_date($date, $format)
{
  return Helpers_Date::format($date, constant("Helpers_Date::" . $format));
}

function __include($uri, $values = array(), $method = Sabel_Request::GET, $withLayout = false)
{
  $requester = new Sabel_Request_Internal($method);
  $requester->values($values)->withLayout($withLayout);
  return $requester->request($uri)->getResult();
}
