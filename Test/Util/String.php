<?php

class String extends Sabel_Util_String {}

/**
 * testcase of sabel.util.String
 *
 * @category  Util
 * @author    Ebine Yutaka <ebine.yutaka@sabel.jp>
 */
class Test_Util_String extends SabelTestCase
{
  public static function suite()
  {
    return self::createSuite("Test_Util_String");
  }
  
  public function testIsEmpty()
  {
    $string = new String();
    $this->assertTrue($string->isEmpty());
  }
  
  public function testNotString()
  {
    try {
      $string = new String(10000);
    } catch (Sabel_Exception_InvalidArgument $e) {
      return;
    }
    
    $this->fail();
  }
  
  public function testCharAt()
  {
    $string = new String("test");
    $this->assertEquals("t", $string->charAt(0)->toString());
    $this->assertEquals("e", $string->charAt(1)->toString());
    $this->assertEquals("t", $string->charAt(3)->toString());
    $this->assertEquals("",  $string->charAt(4)->toString());
    $this->assertEquals("",  $string->charAt(-1)->toString());
    
    $string = new String("あいうえお");
    $this->assertEquals("い", $string->charAt(1)->toString());
    $this->assertEquals("え", $string->charAt(3)->toString());
    $this->assertEquals("",   $string->charAt(5)->toString());
  }
  
  public function testIndexOf()
  {
    $string = new String("Hello World");
    $this->assertEquals(6, $string->indexOf("W"));
    $this->assertEquals(4, $string->indexOf("o"));
    $this->assertEquals(7, $string->indexOf("o", 5));
    
    $string = new String("あいうえお あいうえお");
    $this->assertEquals(2, $string->indexOf("う"));
    $this->assertEquals(5, $string->indexOf(" "));
    $this->assertEquals(8, $string->indexOf("う", 5));
  }
  
  public function testLastChar()
  {
    $string = new String("Hello World");
    $this->assertEquals("d", $string->last()->toString());
    
    $string = new String();
    $this->assertEquals("", $string->last()->toString());
    
    $string = new String("あいうえお");
    $this->assertEquals("お", $string->last()->toString());
  }
  
  public function testTrim()
  {
    $string = new String("  Hello World  ");
    $this->assertTrue($string->trim()->equals("Hello World"));
    
    $string = new String("/*/*/Hello World/*/*/");
    $this->assertTrue($string->trim("/*")->equals("Hello World"));
    
    $str = <<<STR
  
  aiueo
  

  
STR;
    
    $string = new String($str);
    $this->assertTrue($string->trim()->equals("aiueo"));
    
    // multibyte
    
    $string = new String("　　あいうえお　　");
    $this->assertTrue($string->trim()->equals("あいうえお"));
    
    $string = new String("表能申あいうえお申能表");
    $this->assertTrue($string->trim("表能申")->equals("あいうえお"));
    
    $str = <<<STR
 　 　
 　あいうえお　 
　  　


　　 　

　
STR;
    
    $string = new String($str);
    $this->assertTrue($string->trim()->equals("あいうえお"));
  }
  
  public function testRtrim()
  {
    $string = new String("  Hello World  ");
    $this->assertTrue($string->rtrim()->equals("  Hello World"));
    
    $string = new String("　　あいうえお　　");
    $this->assertTrue($string->rtrim()->equals("　　あいうえお"));
  }
  
  public function testLtrim()
  {
    $string = new String("  Hello World  ");
    $this->assertTrue($string->ltrim()->equals("Hello World  "));
    
    $string = new String("　　あいうえお　　");
    $this->assertTrue($string->ltrim()->equals("あいうえお　　"));
  }
  
  public function testToUpperCase()
  {
    $string = new String("Hello World");
    $this->assertTrue($string->toUpperCase()->equals("HELLO WORLD"));
  }
  
  public function testToLowerCase()
  {
    $string = new String("Hello World");
    $this->assertTrue($string->toLowerCase()->equals("hello world"));
  }
  
  public function testUcFirst()
  {
    $string = new String("test");
    $this->assertTrue($string->ucfirst()->equals("Test"));
  }
  
  public function testLcFirst()
  {
    $string = new String("ABCDE");
    $this->assertTrue($string->lcfirst()->equals("aBCDE"));
  }
  
  public function testExplode()
  {
    $string = new String("hoge:fuga:foo:bar");
    $array  = $string->explode(":");
    $this->assertEquals("hoge", $array[0]);
    $this->assertEquals("fuga", $array[1]);
    $this->assertEquals("foo",  $array[2]);
    $this->assertEquals("bar",  $array[3]);
    
    $string = new String("hoge:fuga:foo:bar");
    $array  = $string->explode(":", 3);
    $this->assertEquals("hoge", $array[0]);
    $this->assertEquals("fuga", $array[1]);
    $this->assertEquals("foo:bar", $array[2]);
  }
  
  public function testSplit()
  {
    $string = new String("hoge.fuga.foo.bar");
    $array  = $string->split();
    $this->assertEquals("h", $array[0]);
    $this->assertEquals("f", $array[5]);
    $this->assertEquals("f", $array[10]);
    $this->assertEquals("b", $array[14]);
    
    $array  = $string->split(3);
    $this->assertEquals("hog", $array[0]);
    $this->assertEquals("e.f", $array[1]);
    $this->assertEquals("uga", $array[2]);
    $this->assertEquals(".fo", $array[3]);
    $this->assertEquals("o.b", $array[4]);
    $this->assertEquals("ar",  $array[5]);
    
    // multibyte
    
    $string = new String("あいうえおかきくけこ");
    $array  = $string->split();
    $this->assertEquals("あ", $array[0]);
    $this->assertEquals("う", $array[2]);
    $this->assertEquals("お", $array[4]);
    $this->assertEquals("き", $array[6]);
    $this->assertEquals("け", $array[8]);
    
    $array  = $string->split(3);
    $this->assertEquals("あいう", $array[0]);
    $this->assertEquals("えおか", $array[1]);
    $this->assertEquals("きくけ", $array[2]);
    $this->assertEquals("こ",     $array[3]);
  }
  
  public function testReplace()
  {
    $string = new String("hoge huga");
    $this->assertTrue($string->replace("hoge", "foo")->equals("foo huga"));
  }
  
  public function testAppend()
  {
    $string = new String("hoge");
    $this->assertTrue($string->append("hoge")->equals("hogehoge"));
    
    $hoge = new String("hoge");
    $huga = new String("huga");
    $this->assertTrue($hoge->append($huga)->equals("hogehuga"));
  }
  
  public function testEquals()
  {
    $string = new String("hoge");
    $this->assertTrue($string->equals("hoge"));
    $this->assertTrue($string->equals("huga", "hoge"));
    $this->assertFalse($string->equals("huga"));
    $this->assertFalse($string->equals("huga", "foo"));
    
    $hoge1 = new String("hoge");
    $hoge2 = new String("hoge");
    
    $this->assertTrue($hoge1->equals($hoge2));
  }
  
  public function testSha1()
  {
    $string = new String("hoge");
    $this->assertEquals(sha1("hoge"), $string->sha1()->toString());
  }
  
  public function testMd5()
  {
    $string = new String("hoge");
    $this->assertEquals(md5("hoge"), $string->md5()->toString());
  }
  
  public function testSucc()
  {
    $string = new String("a");
    $this->assertTrue($string->succ()->equals("b"));
    $this->assertTrue($string->succ()->equals("c"));
    
    $string = new String("00");
    $this->assertTrue($string->succ()->equals("01"));
    $this->assertTrue($string->succ()->equals("02"));
    
    $string = new String("99");
    $this->assertTrue($string->succ()->equals("100"));
    $this->assertTrue($string->succ()->equals("101"));
    
    $string = new String("y");
    $this->assertTrue($string->succ()->equals("z"));
    $this->assertTrue($string->succ()->equals("aa"));
    $this->assertTrue($string->succ()->equals("ab"));
    
    $string = new String("Y");
    $this->assertTrue($string->succ()->equals("Z"));
    $this->assertTrue($string->succ()->equals("AA"));
    $this->assertTrue($string->succ()->equals("AB"));
    
    $string = new String("ay");
    $this->assertTrue($string->succ()->equals("az"));
    $this->assertTrue($string->succ()->equals("ba"));
    $this->assertTrue($string->succ()->equals("bb"));
    
    $string = new String("aY");
    $this->assertTrue($string->succ()->equals("aZ"));
    $this->assertTrue($string->succ()->equals("bA"));
    $this->assertTrue($string->succ()->equals("bB"));
    
    $string = new String("0Y");
    $this->assertTrue($string->succ()->equals("0Z"));
    $this->assertTrue($string->succ()->equals("1A"));
    $this->assertTrue($string->succ()->equals("1B"));
    
    $string = new String("9Y");
    $this->assertTrue($string->succ()->equals("9Z"));
    $this->assertTrue($string->succ()->equals("10A"));
    $this->assertTrue($string->succ()->equals("10B"));
    
    $string = new String("A998");
    $this->assertTrue($string->succ()->equals("A999"));
    $this->assertTrue($string->succ()->equals("B000"));
    $this->assertTrue($string->succ()->equals("B001"));
  }
  
  public function testSubString()
  {
    $string = new String("Hello World");
    
    $str = $string->substring(6);
    $this->assertTrue($str->equals("World"));
    $this->assertTrue($string->equals("Hello World"));
    
    $str = $string->substring(6, 3);
    $this->assertTrue($str->equals("Wor"));
    
    $str = $string->substring(1, -1);
    $this->assertTrue($str->equals("ello Worl"));
    
    $string = new String("あいうえおかきくけこ");
    
    $str = $string->substring(5);
    $this->assertTrue($str->equals("かきくけこ"));
    
    $str = $string->substring(6, 3);
    $this->assertTrue($str->equals("きくけ"));
    
    $str = $string->substring(1, -1);
    $this->assertTrue($str->equals("いうえおかきくけ"));
  }
  
  public function testInsert()
  {
    $string = new String("Hello World");
    $string->insert(6, "PHP ");
    $this->assertTrue($string->equals("Hello PHP World"));
    
    $string = new String("Hello World");
    $string->insert(0, "PHP. ");
    $this->assertTrue($string->equals("PHP. Hello World"));
    
    $string = new String("あいうえお　さしすせそ");
    $string->insert(6, "かきくけこ　");
    $this->assertTrue($string->equals("あいうえお　かきくけこ　さしすせそ"));
  }
  
  public function testPad()
  {
    $string = new String("1");
    $this->assertEquals("   1", $string->pad(" ", 4)->toString());
    
    $string = new String("1");
    $this->assertEquals("1   ", $string->pad(" ", 4, STR_PAD_RIGHT)->toString());
    
    $string = new String("1");
    $this->assertEquals("=.=.1", $string->pad("=.", 5)->toString());
    
    $string = new String("1");
    $this->assertEquals("1=.=.", $string->pad("=.", 5, STR_PAD_RIGHT)->toString());
    
    $string = new String("1");
    $this->assertEquals("=.=1", $string->pad("=.", 4)->toString());
    
    $string = new String("1");
    $this->assertEquals("_1_", $string->pad("_", 3, STR_PAD_BOTH)->toString());
    
    $string = new String("1");
    $this->assertEquals("_1__", $string->pad("_", 4, STR_PAD_BOTH)->toString());
    
    $string = new String("1");
    $this->assertEquals("__1__", $string->pad("_", 5, STR_PAD_BOTH)->toString());
    
    $string = new String("123");
    $this->assertEquals("aba123abab", $string->pad("ab", 10, STR_PAD_BOTH)->toString());
    
    // multibyte
    
    $string = new String("あ");
    $this->assertEquals("いいいあ", $string->pad("い", 4)->toString());
    
    $string = new String("あ");
    $this->assertEquals("あいいい", $string->pad("い", 4, STR_PAD_RIGHT)->toString());
    
    $string = new String("あ");
    $this->assertEquals("いういうあ", $string->pad("いう", 5)->toString());
    
    $string = new String("あ");
    $this->assertEquals("あいういう", $string->pad("いう", 5, STR_PAD_RIGHT)->toString());
    
    $string = new String("あ");
    $this->assertEquals("いういあ", $string->pad("いう", 4)->toString());
    
    $string = new String("あ");
    $this->assertEquals("いあい", $string->pad("い", 3, STR_PAD_BOTH)->toString());
    
    $string = new String("あ");
    $this->assertEquals("いあいい", $string->pad("い", 4, STR_PAD_BOTH)->toString());
    
    $string = new String("あ");
    $this->assertEquals("いいあいい", $string->pad("い", 5, STR_PAD_BOTH)->toString());
    
    $string = new String("あいう");
    $this->assertEquals("えおえあいうえおえお", $string->pad("えお", 10, STR_PAD_BOTH)->toString());
  }
  
  public function testClone()
  {
    $string = new String("Hello World");
    $cloned = $string->cloning();
    $this->assertTrue($string == $cloned);
    $this->assertFalse($string === $cloned);
  }
}
