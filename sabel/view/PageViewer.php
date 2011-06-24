<?php

/**
 * Sabel_View_PageViewer
 *
 * @category   Template
 * @package    org.sabel.template
 * @author     Hamanaka Kazuhiro <hamanaka.kazuhiro@sabel.jp>
 * @copyright  2004-2008 Hamanaka Kazuhiro <hamanaka.kazuhiro@sabel.jp>
 * @license    http://www.opensource.org/licenses/bsd-license.php  BSD License
 */
class Sabel_View_PageViewer extends Sabel_Object implements Iterator
{
  protected $pager    = null;
  protected $current  = 1;
  protected $lastPage = 1;
  protected $window   = 10;
  protected $start    = 0;
  protected $end      = 0;
  protected $position = null;
  
  public function __construct(Sabel_View_Pager $pager, $window = null)
  {
    $this->pager    = clone $pager;
    $this->current  = $pager->getPageNumber();
    $this->lastPage = $pager->getTotalPageNumber();
    
    if (is_numeric($window)) {
      $this->window = $window;
    }
  }
  
  public function getCurrent()
  {
    return $this->current;
  }
  
  public function getNext()
  {
    return (int)min($this->lastPage, $this->current + 1);
  }
  
  public function getPrevious()
  {
    return (int)max(1, $this->current - 1);
  }
  
  public function getFirst()
  {
    return 1;
  }
  
  public function getLast()
  {
    return $this->lastPage;
  }
  
  public function isCurrent()
  {
    return ($this->current === $this->pager->getPageNumber());
  }
  
  public function isFirst()
  {
    return ($this->current === 1);
  }
  
  public function isLast()
  {
    return ($this->current === $this->lastPage);
  }
  
  public function hasNext()
  {
    return (!$this->isLast());
  }
  
  public function hasPrevious()
  {
    return (!$this->isFirst());
  }
  
  public function setWindow($size)
  {
    $this->window = (int)$size;
  }
  
  public function current()
  {
    return $this->position;
  }
  
  public function key()
  {
    
  }
  
  public function next()
  {
    $this->position->current++;
  }
  
  public function rewind()
  {
    $this->position = clone $this;
    
    $this->start = (int)$this->current - floor($this->window / 2);
    $this->end   = (int)$this->start + $this->window;
    
    if ($this->start < 1) $this->start = 1;
    
    if (($start = $this->lastPage - $this->end + 1) < 0) {
      $this->start = (int)$this->start + $start;
    }
    
    $this->end = (int)$this->start + $this->window;
    $this->position->current = (int)max(1, $this->start);
  }
  
  public function valid()
  {
    return ($this->position->current < (int)min($this->lastPage + 1, $this->end));
  }
}
