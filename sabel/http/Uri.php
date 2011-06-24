<?php

class Sabel_Http_Uri extends Sabel_ValueObject
{
  public static function fromArray(array $values)
  {
    $self = new self();
    $self->addValues($values);
    
    return $self;
  }
  
  public function __toString()
  {
    $pass  = (strlen($this->pass) > 0) ? ":{$this->pass}" : "";
    $auth  = (strlen($this->user) > 0) ? "{$this->user}{$pass}@" : "";
    $port  = (strlen($this->port) > 0) ? ":{$this->port}" : "";
    $query = (strlen($this->query) > 0) ? "?{$this->query}" : "";
    $frag  = (strlen($this->fragment) > 0) ? "#{$this->fragment}" : "";
    
    return $this->scheme . "://" . $auth . $this->host . $port . $this->path . $query . $frag;
  }
}
