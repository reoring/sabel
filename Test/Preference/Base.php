<?php

/**
 * base test case of sabel Preference package
 *
 * @abstract
 * @category   Preference
 * @package    org.sabel.preference
 * @author     Mori Reo <mori.reo@sabel.jp>
 * @copyright  2004-2008 Mori Reo <mori.reo@sabel.jp>
 * @license    http://www.opensource.org/licenses/bsd-license.php  BSD License
 */
class Test_Preference_Base extends SabelTestCase
{
  protected $pref = null;

  public function testGetInt()
  {
    $this->assertEquals(1, $this->pref->getInt("test", 1));

    $this->assertEquals(2, $this->pref->getInt("test", 2));

    $this->assertEquals(2, $this->pref->getInt("test"));
  }

  public function testGetIntWithString()
  {
    $this->assertEquals(1, $this->pref->getInt("test", "1"));

    $this->pref->setInt("test2", "2");
    $this->assertEquals(2, $this->pref->getInt("test2"));
  }

  public function testBoolean()
  {
    $this->pref->setBoolean("bool1", 1);
    $this->assertTrue($this->pref->getBoolean("bool1"));

    $this->pref->setBoolean("bool2", 0);
    $this->assertFalse($this->pref->getBoolean("bool2"));

    $this->assertTrue($this->pref->getBoolean("bool3", true));
    $this->assertTrue($this->pref->getBoolean("bool4", "true"));
    $this->assertTrue($this->pref->getBoolean("bool5", "t"));
    $this->assertTrue($this->pref->getBoolean("bool6", 1.0));
    $this->assertTrue($this->pref->getBoolean("bool7", "false"));
    $this->assertTrue($this->pref->getBoolean("bool8", "f"));

    $this->assertFalse($this->pref->getBoolean("bool9", false));
    $this->assertFalse($this->pref->getBoolean("bool10", 0.0));
  }
  
  public function testFloat()
  {
    $this->pref->setFloat("float", 1.0);
    $this->assertEquals(1.0, $this->pref->getFloat("float"));
    
    $this->assertEquals(1.1, $this->pref->getFloat("float2", 1.1));
    $this->assertEquals(1.1, $this->pref->getFloat("float2"));
  }

  public function testGetAll()
  {
    $this->pref->setInt("test", 1);
    $this->pref->setString("test1", "str");
    $this->pref->setBoolean("test2", false);
    $this->pref->setFloat("test3", 1.2);

    $result = $this->pref->getAll();

    $this->assertTrue(is_int($result["test"]));
    $this->assertTrue(is_string($result["test1"]));
    $this->assertTrue(is_bool($result["test2"]));
    $this->assertTrue(is_float($result["test3"]));

    $this->assertEquals(1,     $result["test"]);
    $this->assertEquals("str", $result["test1"]);
    $this->assertEquals(false, $result["test2"]);
    $this->assertEquals(1.2,   $result["test3"]);
  }

  public function testArrayType()
  {
    $obj = new StdClass();
    $obj->a = "a";
    $obj->b = 0;
    $obj->c = 1.1;
    $obj->d = false;
    $obj->obj = new StdClass();
    $obj->array = array(0, 1, 2, array(1, 2, 3));

    $assertValue = array("test", 0, 1, 1.0, false, true,
                         array("key" => "value", 0 => 1), array(0, 1, 2),
                         new StdClass(), $obj);

    $this->pref->setArray("test", $assertValue);
    $this->assertEquals($assertValue, $this->pref->getArray("test"));
  }

  public function testObjectType()
  {
    $obj = new TestForObjectType();

    $this->pref->setObject("test", $obj);

    $this->assertEquals($obj, $this->pref->getObject("test"));
  }
  
  public function testObjectTypeWithDefault()
  {
    $obj = new TestForObjectType();

    $obj2 = $this->pref->getObject("test", $obj);

    $this->assertEquals($obj, $this->pref->getObject("test"));
    $this->assertEquals($obj, $obj2);
  }

  public function testDelete()
  {
    $this->pref->setInt("test", 1);

    $this->pref->delete("test");

    try {
      $this->pref->getInt("test");
    } catch (Sabel_Exception_Runtime $e) {
      return;
    }

    $this->fail();
  }

  public function testContains()
  {
    $this->pref->setInt("test", 1);

    $this->assertTrue($this->pref->contains("test"));
    $this->assertFalse($this->pref->contains("test1"));
  }

  public function testGetUndefinedKeyWithNotDefault()
  {
    try {
      $this->pref->getInt("undefined_key");
    } catch (Sabel_Exception_Runtime $e) {
      // exception occured this test pass ok
      return;
    }

    $this->fail();
  }
}

class TestForObjectType
{
  private $name = "string";
  private $age = 11;
  private $height = 192.1;

  private $composite;

  public function __construct()
  {
    $this->composite = new StdClass();
  }

  public function getName()
  {
    return $this->name;
  }
}
