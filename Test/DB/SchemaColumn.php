<?php

/**
 * testcase for sabel.db.schema.Column
 *
 * @category  DB
 * @author    Ebine Yutaka <ebine.yutaka@sabel.jp>
 */
class Test_DB_SchemaColumn extends SabelTestCase
{
  public static function suite()
  {
    return self::createSuite("Test_DB_SchemaColumn");
  }
  
  public function testIntegerCast()
  {
    $ts = MODEL("TestSchema");
    
    $ts->intcol = "0";
    $this->assertEquals(0, $ts->intcol);
    
    $ts->intcol = "10";
    $this->assertEquals(10, $ts->intcol);
    
    $ts->intcol = "100.000";
    $this->assertEquals(100, $ts->intcol);
    
    $ts->intcol = "100.000.000";
    $this->assertEquals("100.000.000", $ts->intcol);
    
    $ts->intcol = 10000000000;
    $this->assertEquals(10000000000, $ts->intcol);
    $this->assertTrue(is_float($ts->intcol));
    
    $ts->intcol = 1000000000.0;
    $this->assertEquals(1000000000, $ts->intcol);
    $this->assertTrue(is_int($ts->intcol));
    
    $ts->intcol = false;
    $this->assertEquals(false, $ts->intcol);
    
    $ts->intcol = true;
    $this->assertEquals(true, $ts->intcol);
  }
  
  public function testSmallIntegerCast()
  {
    $ts = MODEL("TestSchema");
    
    $ts->sintcol = "0";
    $this->assertEquals(0, $ts->sintcol);
    
    $ts->sintcol = "10";
    $this->assertEquals(10, $ts->sintcol);
    
    $ts->sintcol = "100.000";
    $this->assertEquals(100, $ts->sintcol);
    
    $ts->sintcol = "100.000.000";
    $this->assertEquals("100.000.000", $ts->sintcol);
    
    $ts->sintcol = 10000000000;
    $this->assertEquals(10000000000, $ts->sintcol);
    $this->assertTrue(is_float($ts->sintcol));
    
    $ts->sintcol = 10000.0;
    $this->assertEquals(10000, $ts->sintcol);
    $this->assertTrue(is_int($ts->sintcol));
    
    $ts->sintcol = false;
    $this->assertEquals(false, $ts->sintcol);
    
    $ts->sintcol = true;
    $this->assertEquals(true, $ts->sintcol);
  }
  
  public function testBooleanCast()
  {
    $ts = MODEL("TestSchema");
    
    $ts->boolcol = 1;
    $this->assertTrue($ts->boolcol);
    
    $ts->boolcol = "1";
    $this->assertTrue($ts->boolcol);
    
    $ts->boolcol = "t";
    $this->assertTrue($ts->boolcol);
    
    $ts->boolcol = "true";
    $this->assertTrue($ts->boolcol);
    
    $ts->boolcol = 0;
    $this->assertFalse($ts->boolcol);
    
    $ts->boolcol = "0";
    $this->assertFalse($ts->boolcol);
    
    $ts->boolcol = "f";
    $this->assertFalse($ts->boolcol);
    
    $ts->boolcol = "false";
    $this->assertFalse($ts->boolcol);
    
    $ts->boolcol = 10;
    $this->assertEquals(10, $ts->boolcol);
    
    $ts->boolcol = "abc";
    $this->assertEquals("abc", $ts->boolcol);
  }
  
  public function testFloatCast()
  {
    $ts = MODEL("TestSchema");
    
    $ts->floatcol = 1;
    $this->assertTrue(is_float($ts->floatcol));
    $this->assertEquals(1, $ts->floatcol);
    
    $ts->floatcol = "1";
    $this->assertTrue(is_float($ts->floatcol));
    $this->assertEquals(1, $ts->floatcol);
    
    $ts->floatcol = "0.123";
    $this->assertTrue(is_float($ts->floatcol));
    $this->assertEquals(0.123, $ts->floatcol);
    
    $ts->floatcol = "0.123.456";
    $this->assertFalse(is_float($ts->floatcol));
    $this->assertEquals("0.123.456", $ts->floatcol);
    
    $ts->floatcol = true;
    $this->assertFalse(is_float($ts->floatcol));
    $this->assertEquals(true, $ts->floatcol);
  }
}

class Schema_TestSchema
{
  public static function get()
  {
    $cols = array();
    
    $cols['intcol'] = array('type'      => Sabel_Db_Type::INT,
                            'max'       => PHP_INT_MAX,
                            'min'       => -PHP_INT_MAX - 1,
                            'increment' => false,
                            'nullable'  => true,
                            'primary'   => false,
                            'default'   => null);
                             
    $cols['sintcol'] = array('type'      => Sabel_Db_Type::SMALLINT,
                             'max'       => 32767,
                             'min'       => -32768,
                             'increment' => false,
                             'nullable'  => true,
                             'primary'   => false,
                             'default'   => null);
                             
    $cols['boolcol'] = array('type'      => Sabel_Db_Type::BOOL,
                             'increment' => false,
                             'nullable'  => true,
                             'primary'   => false,
                             'default'   => null);
                             
    $cols['floatcol'] = array('type'      => Sabel_Db_Type::FLOAT,
                              'min'       => -3.4028235E+38,
                              'max'       => 3.4028235E+38,
                              'increment' => false,
                              'nullable'  => true,
                              'primary'   => false,
                              'default'   => null);
                              
    return $cols;
  }

  public function getProperty()
  {
    $property = array();
    
    $property["tableEngine"] = null;
    $property["uniques"]     = null;
    $property["fkeys"]       = null;
    
    return $property;
  }
}
