<?php

/**
 * testcase for sabel.view.Pager, sabel.view.PageViewer
 *
 * @category  View
 * @author    Mori Reo <mori.reo@sabel.jp>
 */
class Test_View_PageViewer extends SabelTestCase
{
  public static function suite()
  {
    return self::createSuite("Test_View_PageViewer");
  }
  
  public function testStandardUse()
  {
    $pager = new Sabel_View_Pager(200, 10);
    $pager->setPageNumber(10);
    $pv = new Sabel_View_PageViewer($pager);
    
    $this->assertTrue($pv->isCurrent());
    $this->assertFalse($pv->isFirst());
    
    $this->assertEquals(10, $pv->getCurrent());
    $this->assertEquals(11, $pv->getNext());
    $this->assertEquals(9, $pv->getPrevious());
    
    $num = 5;
    foreach ($pv as $page) $this->assertEquals($num++, $page->getCurrent());
    $this->assertEquals(15, $num);
    $this->assertEquals(10, $pv->getCurrent());
    $this->assertEquals(11, $pv->getNext());
    $this->assertEquals(9, $pv->getPrevious());
    
    $this->assertEquals(20, $pv->getLast());
    $this->assertEquals(1,  $pv->getFirst());
  }
  
  public function testStandardUseAndPageNumberFirst()
  {
    $pager = new Sabel_View_Pager(200, 10);
    $pager->setPageNumber(1);
    $pv = new Sabel_View_PageViewer($pager, 5);
    
    $this->assertTrue($pv->isFirst());
    $this->assertTrue($pv->isCurrent());
    $this->assertFalse($pv->isLast());
    
    $this->assertEquals(1, $pv->getCurrent());
    $this->assertEquals(2, $pv->getNext());
    $this->assertEquals(1, $pv->getPrevious());
    
    $num = 1;
    foreach ($pv as $page) $this->assertEquals($num++, $page->getCurrent());
    $this->assertEquals(6, $num);
    $this->assertEquals(1, $pv->getCurrent());
    $this->assertEquals(2, $pv->getNext());
    $this->assertEquals(1, $pv->getPrevious());
  }
  
  // @todo more tests
}
