<?php

class Sabel_Request_File extends Sabel_ValueObject
{
  public function __toString()
  {
    return $this->getContent();
  }
  
  public function isEmpty()
  {
    return (is_empty($this->path) || $this->size <= 0);
  }
  
  public function getContent()
  {
    if (is_empty($this->path)) {
      return "";
    } else {
      return file_get_contents($this->path);
    }
  }
}
