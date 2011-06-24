<?php

/**
 * Sabel_View_Pager
 *
 * @category   Template
 * @package    org.sabel.template
 * @author     Hamanaka Kazuhiro <hamanaka.kazuhiro@sabel.jp>
 * @author     Ebine Yutaka <ebine.yutaka@sabel.jp>
 * @copyright  2004-2008 Hamanaka Kazuhiro <hamanaka.kazuhiro@sabel.jp>
 * @license    http://www.opensource.org/licenses/bsd-license.php  BSD License
 */
class Sabel_View_Pager extends Sabel_Object
{
  private $pageItem     = 25;
  private $numberOfItem = 0;
  private $pageNumber   = 0;
  private $totalPageNumber = 0;
  
  public function __construct($count, $limit)
  {
    $this->setNumberOfItem($count);
    $this->setLimit($limit);
    
    $this->totalPageNumber = (int)ceil(max($this->numberOfItem / $this->pageItem, 1));
  }
  
  public function setNumberOfItem($num)
  {
    if ($num < 0 || !is_numeric($num)) {
      $message = __METHOD__ . "() invalid number of item: $num";
      throw new Sabel_Exception_InvalidArgument($message);
    }
    
    $this->numberOfItem = (int)$num;
    
    return $this;
  }
  
  public function getNumberOfItem()
  {
    return $this->numberOfItem;
  }
  
  public function setLimit($limit)
  {
    $this->pageItem = (int)max($limit, 1);
    
    return $this;
  }
  
  public function getLimit()
  {
    return $this->pageItem;
  }
  
  public function setPageNumber($page)
  {
    if ($page > $this->totalPageNumber) {
      $this->pageNumber = $this->totalPageNumber;
    } else {
      $this->pageNumber = (int)max($page, 1);
    }
    
    return $this;
  }
  
  public function getPageNumber()
  {
    return $this->pageNumber;
  }
  
  public function getTotalPageNumber()
  {
    return $this->totalPageNumber;
  }
  
  public function getSqlOffset()
  {
    return (int)floor($this->pageItem * ($this->getPageNumber() - 1));
  }
}
