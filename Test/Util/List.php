<?php

/**
 * testcase of sabel.util.LinkedList
 *
 * @category  Util
 * @author    Mori Reo <mori.reo@sabel.jp>
 */
class Test_Util_LinkedList extends SabelTestCase
{
  private $list = null;
  
  public static function suite()
  {
    return self::createSuite("Test_Util_LinkedList");
  }
  
  public function setUp()
  {
    $first = new StdClass();
    $first->name = "first";
    
    $second = new StdClass();
    $second->name = "second";
    
    $third = new StdClass();
    $third->name = "third";
    
    $this->list = new Sabel_Util_LinkedList("first", $first);
    
    $this->list->insertNext("second", $second)
               ->insertNext("third", $third);
  }
  
  public function testInsertPreviousAndNext()
  {
    $list = new Sabel_Util_LinkedList("test", new StdClass());
    
    for ($i=0; $i < 299; $i++) {
      $list->insertNext("test{$i}", new StdClass());
    }
    $this->assertEquals(300, $list->size());
    
    for ($i=0; $i<300; $i++) {
      $list->insertPrevious("test{$i}", new StdClass());
    }
    $this->assertEquals(600, $list->size());
  }
  
  public function testFindByName()
  {
    $list = new Sabel_Util_LinkedList("test", new StdClass());
    
    $target = new StdClass();
    $target->value = "ebine";
    $next = $list->insertNext("target", $target);
    $next->insertNext("test2", new StdClass());
    
    $obj = $list->find("target");
    
    $this->assertTrue(($obj instanceof Sabel_Util_LinkedList));
    $this->assertTrue(is_object($obj));
    $this->assertEquals("ebine", $obj->current->value);
    $this->assertEquals("test", $obj->getFirst()->name);
    $this->assertEquals("ebine", $obj->getFirst()->next->current->value);
    $this->assertEquals("test2", $obj->getFirst()->next->next->name);
  }
  
  public function testUnlink()
  {
    $list = $this->list;
    $list->getFirst()->next()->unlink();
    $this->assertEquals("first", $list->getFirst()->name);
    $this->assertEquals("third", $list->getFirst()->next()->name);
  }
  
  public function testUnlinkWithFind()
  {
    $list = $this->list;
    $list->find("second")->unlink();
    $this->assertEquals("first", $list->getFirst()->name);
    $this->assertEquals("third", $list->getFirst()->next()->name);
  }
  
  public function testUnlinkAndInsert()
  {
    $list = $this->list;
    
    $list->find("second")->unlink();
    $list->getFirst()->insertNext("second", new StdClass());
    $this->assertEquals("second", $list->getFirst()->next()->name);
  }
  
  public function testUnlinkLast()
  {
    $this->list->getLast()->unlink();
    $this->assertEquals("second", $this->list->getLast()->name);
  }
  
  public function testInsertNextPreviousPointer()
  {
    $list = $this->list;
    $list->find("third")->insertNext("force", new StdClass());
    $this->assertEquals("third", $list->getLast()->previous->name);
  }
}
