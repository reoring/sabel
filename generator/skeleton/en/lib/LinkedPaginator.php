<?php

class LinkedPaginator extends Paginator
{
  protected $lastPage = 20;
  protected $hasNext = false;
  
  public function setLastPageNumber($num)
  {
    $this->lastPage = $num;
  }
  
  public function prev($text, $attrs = array())
  {
    return $this->createLink($text, $this->getUriQuery($this->viewer->getPrevious()), $attrs);
  }
  
  public function next($text, $attrs = array())
  {
    return $this->createLink($text, $this->getUriQuery($this->viewer->getNext()), $attrs);
  }
  
  public function hasPrev()
  {
    $attrs = $this->attributes;
    return ($attrs[$attrs["pageKey"]] > 1);
  }
  
  public function hasNext()
  {
    return $this->hasNext;
  }
  
  public function build($limit, array $getValues = array())
  {
    $page = 1;
    $pageKey = $this->attributes["pageKey"];
    
    if (isset($getValues[$pageKey])) {
      $page = $getValues[$pageKey];
      if (!is_numeric($page) || $page < 1) $page = 1;
    }
    
    $model = $this->model;
    $attributes =& $this->attributes;
    
    unset($getValues[$pageKey]);
    unset($getValues[ini_get("session.name")]);
    $attributes["uriQuery"] = http_build_query($getValues, "", "&");
    
    $count = $this->lastPage * $limit;
    
    $attributes["count"]  = $count;
    $attributes["limit"]  = $limit;
    $attributes[$pageKey] = $page;
    
    $pager = new Sabel_View_Pager($count, $limit);
    $pager->setPageNumber($page);
    $attributes["viewer"] = new Sabel_View_PageViewer($pager);
    
    if ($count === 0) {
      $attributes["offset"]  = 0;
      $attributes["results"] = array();
      $model->clear();
    } else {
      $offset = $pager->getSqlOffset();
      $this->_setOrderBy($getValues);
      $model->setLimit($limit + 1);
      $model->setOffset($offset);
      
      $attributes["offset"]  = $offset;
      $attributes["results"] = $model->{$this->method}();
      
      $results = $attributes["results"];
      if (count($results) > $limit) {
        array_pop($results);
        $attributes["results"] = $results;
        
        if ($page < $this->lastPage) {
          $this->hasNext = true;
        }
      }
    }
    
    return $this;
  }
  
  protected function createLink($text, $query, $attrs)
  {
    $_attrs = "";
    if (is_array($attrs) && !empty($attrs)) {
      $tmp = array();
      foreach ($attrs as $attr => $value) {
        $tmp[] = $attr . '="' . h($value) . '"';
      }
      
      $_attrs = " " . implode(" ", $tmp);
    }
    
    $format = '<a%s href="%s?%s">%s</a>';
    return sprintf($format, $_attrs, $this->uri, $query, h($text));
  }
}
