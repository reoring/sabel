<?php

class UtilMap extends Sabel_Util_Map {}

/**
 * testcase of sabel.util.Map
 *
 * @category  Util
 * @author    Ebine Yutaka <ebine.yutaka@sabel.jp>
 */
class Test_Util_Map extends SabelTestCase
{
  public static function suite()
  {
    return self::createSuite("Test_Util_Map");
  }
  
  public function testIsEmpty()
  {
    $map = new UtilMap();
    $this->assertTrue($map->isEmpty());
  }
  
  public function testCount()
  {
    $map = new UtilMap();
    $this->assertEquals($map->count(), 0);
    
    $map->set(array("test1" => "hoge", "test2" => "fuga", "test3" => "foo"));
    $this->assertEquals($map->count(), 3);
  }
  
  public function testForeach()
  {
    $data = array("test1" => "hoge", "test2" => "fuga");
    $map = new UtilMap($data);
    
    $through = false;
    foreach ($map as $key => $value) {
      if ($through) {
        $this->assertEquals($key, "test2");
        $this->assertTrue($value->equals("fuga"));
      } else {
        $this->assertEquals($key, "test1");
        $this->assertTrue($value->equals("hoge"));
      }
      $through = true;
    }
    
    $this->assertTrue($through);
  }
  
  public function testIterator()
  {
    $data = array("test1" => "hoge", "test2" => "fuga");
    $map = new UtilMap($data);
    
    $through = false;
    while ($map->valid()) {
      if ($through) {
        $this->assertTrue($map->current()->equals("fuga"));
      } else {
        $this->assertTrue($map->current()->equals("hoge"));
      }
      $map->next();
      $through = true;
    }
    
    $this->assertTrue($through);
  }
  
  public function testIterator2()
  {
    $data = array("test1" => "hoge", "test2" => "fuga");
    $map = new UtilMap($data);
    
    $through = false;
    while ($map->hasMoreElements()) {
      if ($through) {
        $this->assertTrue($map->nextElement()->equals("fuga"));
      } else {
        $this->assertTrue($map->nextElement()->equals("hoge"));
      }
      $through = true;
    }
    
    $this->assertTrue($through);
  }
  
  public function testValues()
  {
    $data = array("test1" => "hoge", "test2" => "fuga");
    $map = new UtilMap($data);
    $values = $map->values();
    $this->assertEquals(count($values), 2);
    $this->assertTrue($values[0]->equals("hoge"));
    $this->assertTrue($values[1]->equals("fuga"));
  }
  
  public function testKeys()
  {
    $data = array("test1" => "hoge", "test2" => "fuga");
    $map = new UtilMap($data);
    $keys = $map->keys();
    $this->assertEquals(count($keys), 2);
    $this->assertEquals($keys[0], "test1");
    $this->assertEquals($keys[1], "test2");
  }
  
  public function testImplode()
  {
    $data = array("test1" => "hoge", "test2" => "fuga");
    $map = new UtilMap($data);
    $this->assertTrue($map->implode()->equals("hoge, fuga"));
    $this->assertTrue($map->implode(".")->equals("hoge.fuga"));
  }
  
  public function testPut()
  {
    $map = new UtilMap();
    $map->put("test1", "hoge")
        ->put("test2", "fuga");
        
    $this->assertEquals($map->count(), 2);
    $this->assertTrue($map->get("test1")->equals("hoge"));
    $this->assertTrue($map->get("test2")->equals("fuga"));
    $this->assertNull($map->get("test3"));
  }
  
  public function testPush()
  {
    $fuga = new String("fuga");
    $um   = new UtilMap(array("test" => "foo"));
    
    $map = new UtilMap();
    $map->push("hoge")->push($fuga)->push($um);
    $this->assertEquals($map->count(), 3);
    
    $this->assertTrue($map->get(0)->equals("hoge"));
    $this->assertTrue($map->get(1)->equals("fuga"));
    $this->assertTrue($map->get(1)->equals($fuga));
    $this->assertTrue($map->get(2)->equals($um));
    $this->assertNull($map->get(3));
  }
  
  public function testRemove()
  {
    $map = new UtilMap();
    $map->put("test", "hoge")
        ->put(new UtilMap(array(1)), "fuga");
    
    $this->assertEquals(2, $map->count());
    
    $map->remove("test");
    $this->assertEquals(1, $map->count());
    $this->assertNull($map->get("test"));
    
    $map->remove(new UtilMap());
    $this->assertEquals(1, $map->count());
    $map->remove(new UtilMap(array(2)));
    $this->assertEquals(1, $map->count());
    
    $this->assertTrue($map->get(new UtilMap(array(1)))->equals("fuga"));
    
    $map->remove(new UtilMap(array(1)));
    $this->assertEquals(0, $map->count());
    
    $this->assertNull($map->get(new UtilMap(array(1))));
  }
  
  public function testClear()
  {
    $map = new UtilMap();
    $map->push("hoge")->push("fuga");
    $this->assertEquals($map->count(), 2);
    
    $array = $map->clear();
    $this->assertEquals($map->count(), 0);
    $this->assertTrue(is_array($array));
  }
  
  public function testCannotConvertToString()
  {
    $map = new UtilMap();
    
    try {
      $map->put(new stdClass(), "hoge");
    } catch (Sabel_Exception_Runtime $e) {
      return;
    }
    
    $this->fail();
  }
  
  public function testGet()
  {
    $map = new UtilMap();
    $map->put("test1", "hoge")
        ->put("test2", new String("fuga"))
        ->put(new String("test3"), "foo")
        ->put(new UtilMap(array(1)), "bar")
        ->put(new UtilMap(array(1, 2)), new UtilMap(array(3, 4)));
        
    $this->assertTrue($map->get("test1")->equals("hoge"));
    $this->assertTrue($map->get("test2")->equals("fuga"));
    $this->assertTrue($map->get("test3")->equals("foo"));
    $this->assertTrue($map->get(new String("test3"))->equals("foo"));
    $this->assertTrue($map->get(new UtilMap(array(1)))->equals("bar"));
    
    $um = new UtilMap(array(3, 4));
    $this->assertTrue($map->get(new UtilMap(array(1, 2)))->equals($um));
    
    $this->assertNull($map->get("test4"));
    $this->assertNull($map->get(new UtilMap(array(3, 4))));
  }
  
  public function testSort()
  {
    $fruits = array("lemon", "orange", "banana", "apple");
    $map = new UtilMap($fruits);
    $map->sort();
    
    $this->assertTrue($map->get(0)->equals("apple"));
    $this->assertTrue($map->get(1)->equals("banana"));
    $this->assertTrue($map->get(2)->equals("lemon"));
    $this->assertTrue($map->get(3)->equals("orange"));
  }
  
  public function testRsort()
  {
    $fruits = array("lemon", "orange", "banana", "apple");
    $map = new UtilMap($fruits);
    $map->rsort();
    
    $this->assertTrue($map->get(0)->equals("orange"));
    $this->assertTrue($map->get(1)->equals("lemon"));
    $this->assertTrue($map->get(2)->equals("banana"));
    $this->assertTrue($map->get(3)->equals("apple"));
  }
  
  public function testReverse()
  {
    $fruits = array("lemon", "orange", "banana", "apple");
    $map = new UtilMap($fruits);
    $map->reverse();
    
    $this->assertTrue($map->get(0)->equals("apple"));
    $this->assertTrue($map->get(1)->equals("banana"));
    $this->assertTrue($map->get(2)->equals("orange"));
    $this->assertTrue($map->get(3)->equals("lemon"));
  }
  
  public function testMerge()
  {
    $data = array("test1" => "hoge", "test2" => "fuga");
    $map = new UtilMap($data);
    $this->assertEquals($map->count(), 2);
    
    $data = array("test3" => "foo", "test4" => "bar");
    $map->merge($data);
    $this->assertEquals($map->count(), 4);
    
    $values = $map->values();
    
    $this->assertTrue($values[0]->equals("hoge"));
    $this->assertTrue($values[1]->equals("fuga"));
    $this->assertTrue($values[2]->equals("foo"));
    $this->assertTrue($values[3]->equals("bar"));
    
    $data = array("test5" => "biz", "test6" => "buz");
    $map->merge(new UtilMap($data));
    $this->assertEquals($map->count(), 6);
    
    $values = $map->values();
    
    $this->assertTrue($values[0]->equals("hoge"));
    $this->assertTrue($values[1]->equals("fuga"));
    $this->assertTrue($values[2]->equals("foo"));
    $this->assertTrue($values[3]->equals("bar"));
    $this->assertTrue($values[4]->equals("biz"));
    $this->assertTrue($values[5]->equals("buz"));
  }
  
  public function testUnique()
  {
    $data = array("test1" => "hoge", "test2" => "fuga");
    $map = new UtilMap($data);
    $map->put("test4", "foo")
        ->put("test5", "hoge")
        ->put("test6", "fuga");
        
    $this->assertEquals($map->count(), 5);
    $this->assertEquals($map->unique()->count(), 3);
  }
  
  public function testSum()
  {
    $data = array("test1" => 2, "test2" => 3, "test3" => 5);
    $map = new UtilMap($data);
    $this->assertEquals($map->sum(), 10);
    
    $data = array("test1" => 2.2, "test2" => 5.3, "test3" => 2.5);
    $this->assertEquals($map->sum(), 10.0);
  }
  
  public function testHas()
  {
    $map = new UtilMap();
    $map->put("test1", "hoge")
        ->put("test2", null)
        ->put(new UtilMap(), "fuga");
    
    $this->assertTrue($map->has("test1"));
    $this->assertFalse($map->has("test2"));
    $this->assertTrue($map->exists("test2"));
    $this->assertTrue($map->has(new UtilMap()));
    
    $this->assertFalse($map->has("test4"));
    $this->assertFalse($map->has(new UtilMap(array(1))));
  }
  
  public function testPop()
  {
    $map = new UtilMap();
    $map->put("test1", "hoge")
        ->put("test2", "fuga");
        
    $this->assertTrue($map->has("test1"));
    $this->assertTrue($map->has("test2"));
    
    $this->assertTrue($map->pop()->equals("fuga"));
    $this->assertTrue($map->has("test1"));
    $this->assertFalse($map->has("test2"));
    $this->assertEquals($map->count(), 1);
  }
  
  public function testShift()
  {
    $map = new UtilMap();
    $map->put("test1", "hoge")
        ->put("test2", "fuga");
        
    $this->assertTrue($map->has("test1"));
    $this->assertTrue($map->has("test2"));
    
    $this->assertTrue($map->shift()->equals("hoge"));
    $this->assertFalse($map->has("test1"));
    $this->assertTrue($map->has("test2"));
    $this->assertEquals($map->count(), 1);
  }
  
  public function testSearch()
  {
    $map = new UtilMap();
    $map->put("test1", "hoge")
        ->put("test2", new String("bar"))
        ->put(new String("test3"), "biz")
        ->put("test4", new UtilMap(array(1)))
        ->put("test5", new UtilMap(array("test" => "buz")))
        ->put(new UtilMap(array(1, 2, 3)), "hello world");
        
    $this->assertTrue($map->search("hoge")->equals("test1"));
    $this->assertFalse($map->search("hogehoge"));
    
    $this->assertTrue($map->search("bar")->equals("test2"));
    $this->assertTrue($map->search(new String("bar"))->equals("test2"));
    
    $this->assertTrue($map->search("biz")->equals("test3"));
    $this->assertTrue($map->search(new String("biz"))->equals("test3"));
    
    $this->assertTrue($map->search(new UtilMap(array(1)))->equals("test4"));
    $this->assertTrue($map->search(new UtilMap(array("test" => "buz")))->equals("test5"));
    $this->assertFalse($map->search(new UtilMap(array(2))));
    $this->assertFalse($map->search(new UtilMap(array("test" => "abc"))));
    
    $um = new UtilMap(array(1, 2, 3));
    $this->assertTrue($map->get($um)->equals("hello world"));
  }
}
