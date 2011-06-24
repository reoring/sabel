<?php

function h($string, $charset = APP_ENCODING)
{
  return htmlescape($string, $charset);
}

function xh($string, $charset = APP_ENCODING)
{
  return xmlescape($string, $charset);
}

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

/**
 * create uri for css, image, js, etc...
 */
function linkto($file)
{
  if ($bus = Sabel_Context::getContext()->getBus()) {
    if ($bus->get("NO_VIRTUAL_HOST") && defined("URI_PREFIX")) {
      return URI_PREFIX . "/" . $file;
    }
  }
  
  return "/" . $file;
}

function get_uri_prefix($secure = false, $absolute = false)
{
  $prefix = "";
  
  if ($secure || $absolute) {
    $server = get_server_name();
    $prefix = (($secure) ? "https" : "http") . "://" . $server;
  }
  
  if ($bus = Sabel_Context::getContext()->getBus()) {
    if ($bus->get("NO_VIRTUAL_HOST") && defined("URI_PREFIX")) {
      $prefix .= URI_PREFIX;
    }
  }
  
  return $prefix;
}

/**
 * create uri
 */
function uri($uri, $secure = false, $absolute = false)
{
  $context = Sabel_Context::getContext();
  return get_uri_prefix($secure, $absolute) . "/" . $context->getCandidate()->uri($uri);
}

/**
 * Internal request.
 */
function __include($uri, $values = array(), $method = Sabel_Request::GET, $withLayout = false)
{
  $requester = new Sabel_Request_Internal($method);
  $requester->values($values)->withLayout($withLayout);
  return $requester->request($uri)->getResult();
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
