<?php

/**
 * testcase for sabel.view.Pager
 *
 * @category  View
 * @author    Mori Reo <mori.reo@sabel.jp>
 */
class Test_View_Pager extends SabelTestCase
{
  private $pager = null;
  
  public static function suite()
  {
    return self::createSuite("Test_View_Pager");
  }
  
  public function testStandardPagerUse()
  {
    $pager = new Sabel_View_Pager(100, 10);
    $pager->setPageNumber(3);
    
    $this->assertEquals(100, $pager->getNumberOfItem());
    $this->assertEquals(10,  $pager->getLimit());
    $this->assertEquals(3,   $pager->getPageNumber());
    $this->assertEquals(10,  $pager->getTotalPageNumber());
    $this->assertEquals(20,  $pager->getSqlOffset());
  }
  
  public function testPageNumberRoundPagerUse()
  {
    $pager = new Sabel_View_Pager(300, 70);
    $pager->setPageNumber(100);
    
    $this->assertEquals(300, $pager->getNumberOfItem());
    $this->assertEquals(70,  $pager->getLimit());
    $this->assertEquals(5,   $pager->getPageNumber());
    $this->assertEquals(5,   $pager->getTotalPageNumber());
    $this->assertEquals(280, $pager->getSqlOffset());
  }
  
  public function testExceptedPagerUse()
  {
    try {
      $pager = new Sabel_View_Pager(-1, 10);
    } catch (Sabel_Exception_InvalidArgument $e) {
      return;
    }
    
    $this->fail('set number of item method not thrown.');
  }
  
  public function testExceptedPagerUse2()
  {
    try {
      $pager = new Sabel_View_Pager('a', 10);
    } catch (Sabel_Exception_InvalidArgument $e) {
      return;
    }
    
    $this->fail('set number of item method not thrown.');
  }
  
  public function testUnusualPagerUse()
  {
    $pager = new Sabel_View_Pager(1, 1);
    $pager->setPageNumber(10);
    
    $this->assertEquals(1, $pager->getPageNumber());
    $this->assertEquals(1, $pager->getTotalPageNumber());
    $this->assertEquals(0, $pager->getSqlOffset());
    
    $pager = new Sabel_View_Pager(250, 15);
    $pager->setPageNumber(10);
    
    $this->assertEquals(250, $pager->getNumberOfItem());
    $this->assertEquals(15,  $pager->getLimit());
    $this->assertEquals(10,  $pager->getPageNumber());
    $this->assertEquals(17,  $pager->getTotalPageNumber());
    $this->assertEquals(135, $pager->getSqlOffset());
  }
  
  public function testInitializedPagerUse()
  {
    $pager = new Sabel_View_Pager(200, 20);
    
    $pager->setPageNumber(4.3);
    
    $this->assertEquals(200, $pager->getNumberOfItem());
    $this->assertEquals(20,  $pager->getLimit());
    $this->assertEquals(4,   $pager->getPageNumber());
    $this->assertEquals(10,   $pager->getTotalPageNumber());
    $this->assertEquals(60,  $pager->getSqlOffset());
  }
}
