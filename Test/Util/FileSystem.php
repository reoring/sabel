<?php

/**
 * @category  Util
 * @author    Ebine Yutaka <ebine.yutaka@sabel.jp>
 */
class Test_Util_FileSystem extends SabelTestCase
{
  protected $basedir = "";
  
  public static function suite()
  {
    return self::createSuite("Test_Util_FileSystem");
  }
  
  public function setUp()
  {
    $this->basedir = SABEL_BASE . DS . "Test" . DS . "data" . DS . "application" . DS . "data";
  }
  
  public function testMakedir()
  {
    $fs = new Sabel_Util_FileSystem($this->basedir);
    
    $dir = $fs->mkdir("test");
    $this->assertEquals($this->basedir . DS . "test", $dir->pwd());
    
    $this->assertTrue($fs->isDir("test"));
    $this->assertTrue($fs->isDir($this->basedir . DS . "test"));
    $this->assertTrue(is_dir($this->basedir . DS . "test"));
  }
  
  public function testRecursiveMakedir()
  {
    $path = "hoge" . DS . "fuga" . DS . "foo";
    $fs = new Sabel_Util_FileSystem($this->basedir . DS . "test");
    
    $dir = $fs->mkdir($path);
    $this->assertEquals($this->basedir . DS . "test" . DS . $path, $dir->pwd());
    
    $this->assertTrue($fs->isDir($path));
    $this->assertTrue($fs->isDir($this->basedir . DS . "test" . DS . $path));
    $this->assertTrue(is_dir($this->basedir . DS . "test" . DS . $path));
  }
  
  public function testChangeDirectory()
  {
    $fs = new Sabel_Util_FileSystem($this->basedir . DS . "test");
    $this->assertTrue($fs->isDir("hoge"));
    $this->assertFalse($fs->isDir("fuga"));
    $this->assertFalse($fs->isDir("foo"));
    
    $fs->cd("hoge");
    $this->assertFalse($fs->isDir("hoge"));
    $this->assertTrue($fs->isDir("fuga"));
    $this->assertFalse($fs->isDir("foo"));
    
    $fs->cd("fuga");
    $this->assertFalse($fs->isDir("hoge"));
    $this->assertFalse($fs->isDir("fuga"));
    $this->assertTrue($fs->isDir("foo"));
  }
  
  public function testPwd()
  {
    $fs = new Sabel_Util_FileSystem($this->basedir . DS . "test");
    $this->assertEquals($this->basedir . DS . "test", $fs->pwd());
    
    $fs->cd("hoge");
    $this->assertEquals($this->basedir . DS . "test" . DS . "hoge", $fs->pwd());
    
    $fs->cd("fuga");
    $this->assertEquals($this->basedir . DS . "test" . DS . "hoge" . DS . "fuga", $fs->pwd());
  }
  
  public function testRmdir()
  {
    $fs = new Sabel_Util_FileSystem($this->basedir . DS . "test");
    $fs->cd("hoge" . DS . "fuga");
    
    $this->assertTrue($fs->isDir("foo"));
    $fs->rmdir("foo");
    $this->assertFalse($fs->isDir("foo"));
  }
  
  public function testRecursiveRmdir()
  {
    $fs = new Sabel_Util_FileSystem($this->basedir . DS . "test");
    $this->assertTrue($fs->isDir("hoge"));
    $this->assertTrue($fs->isDir("hoge" . DS . "fuga"));
    
    $fs->rmdir("hoge");
    
    $this->assertFalse($fs->isDir("hoge"));
    $this->assertFalse($fs->isDir("hoge" . DS . "fuga"));
  }
  
  public function testMkfile()
  {
    $fs = new Sabel_Util_FileSystem($this->basedir . DS . "test");
    $fs->mkfile("hoge.txt");
    $this->assertTrue($fs->isFile("hoge.txt"));
    $this->assertTrue($fs->isFile($this->basedir . DS . "test" . DS . "hoge.txt"));
    $this->assertTrue(is_file($this->basedir . DS . "test" . DS . "hoge.txt"));
  }
  
  public function testRecursiveMakeFile()
  {
    $file = "hoge" . DS . "fuga" . DS . "foo.txt";
    $fs = new Sabel_Util_FileSystem($this->basedir . DS . "test");
    $fs->mkfile($file);
    $this->assertTrue($fs->isFile($file));
    $this->assertTrue($fs->isFile($this->basedir . DS . "test" . DS . $file));
    $this->assertTrue(is_file($this->basedir . DS . "test" . DS . $file));
  }
  
  public function testFilePermission()
  {
    // win
    if (DIRECTORY_SEPARATOR === "\\") return;
    
    $file = "hoge" . DS . "fuga" . DS . "foo.txt";
    $fs = new Sabel_Util_FileSystem($this->basedir . DS . "test");
    $file = $fs->getFile($file);
    $this->assertEquals(0755, $file->getPermission());
    $file->chmod(0777);
    $this->assertEquals(0777, $file->getPermission());
  }
  
  public function testFilePermission2()
  {
    // win
    if (DIRECTORY_SEPARATOR === "\\") return;
    
    chmod($this->basedir . DS . "readable.txt", 0444);
    chmod($this->basedir . DS . "writable.txt", 0222);
    chmod($this->basedir . DS . "executable.txt", 0111);
    
    $fs = new Sabel_Util_FileSystem($this->basedir);
    
    $readable = $fs->getFile("readable.txt");
    $this->assertTrue($readable->isReadable());
    $this->assertFalse($readable->isWritable());
    $this->assertFalse($readable->isExecutable());
    
    $writable = $fs->getFile("writable.txt");
    $this->assertFalse($writable->isReadable());
    $this->assertTrue($writable->isWritable());
    $this->assertFalse($writable->isExecutable());
    
    $executable = $fs->getFile("executable.txt");
    $this->assertFalse($executable->isReadable());
    $this->assertFalse($executable->isWritable());
    $this->assertTrue($executable->isExecutable());
    
    chmod($this->basedir . DS . "readable.txt", 0777);
    chmod($this->basedir . DS . "writable.txt", 0777);
    chmod($this->basedir . DS . "executable.txt", 0777);
  }
  
  public function testDirectoryPermission()
  {
    // win
    if (DIRECTORY_SEPARATOR === "\\") return;
    
    $fs = new Sabel_Util_FileSystem($this->basedir . DS . "test");
    $dir = $fs->getDirectory("hoge" . DS . "fuga");
    $this->assertEquals(0755, $dir->getPermission());
    $dir->chmod(0777);
    $this->assertEquals(0777, $dir->getPermission());
  }
  
  public function testFileSize()
  {
    $file = "hoge" . DS . "fuga" . DS . "foo.txt";
    $fs = new Sabel_Util_FileSystem($this->basedir . DS . "test");
    $file = $fs->getFile($file);
    $currentSize = $file->getSize();
    $file->write("abcdefg")->save();
    $this->assertTrue($file->getSize() > $currentSize);
  }
  
  public function testFileContents()
  {
    $file = "hoge" . DS . "fuga" . DS . "foo.txt";
    $fs = new Sabel_Util_FileSystem($this->basedir . DS . "test");
    $file = $fs->getFile($file);
    $this->assertEquals("abcdefg", $file->getContents());
    
    $file->open();
    $file->write("hijklmn")->save();
    $this->assertEquals("abcdefg" . PHP_EOL . "hijklmn", $file->getContents());
  }
  
  public function testFileContentsAsArray()
  {
    $file = "hoge" . DS . "fuga" . DS . "foo.txt";
    $fs = new Sabel_Util_FileSystem($this->basedir . DS . "test");
    $lines = $fs->getFile($file)->getContentsAsArray();
    
    $this->assertEquals(2, count($lines));
    $this->assertEquals("abcdefg", $lines[0]);
    $this->assertEquals("hijklmn", $lines[1]);
    $this->assertFalse(isset($lines[2]));
  }
  
  public function testClearContents()
  {
    $file = "hoge" . DS . "fuga" . DS . "foo.txt";
    $fs = new Sabel_Util_FileSystem($this->basedir . DS . "test");
    $file = $fs->getFile($file);
    $file->clearContents();
    $this->assertEquals("", $file->getContents());
  }
  
  public function testFileCopy()
  {
    $fs = new Sabel_Util_FileSystem($this->basedir . DS . "test" . DS . "hoge" . DS . "fuga");
    $file = $fs->getFile("foo.txt");
    $file->copyTo(".." . DS . "test" . DS . "foo2.txt");
    
    $fs->cd(".." . DS . "test");
    $this->assertTrue($fs->isFile("foo2.txt"));
    
    $file->copyTo(".." . DS . "test" . DS . "foo.txt");
    $this->assertTrue($fs->isFile("foo.txt"));
  }
  
  public function testFileMove()
  {
    $fs = new Sabel_Util_FileSystem($this->basedir . DS . "test");
    $file = $fs->getFile("hoge" . DS . "test" . DS . "foo2.txt");
    $moved = $file->moveTo(".." . DS . "test2" . DS . "foo2.txt");
    
    $fs->cd(".." . DS . "test" . DS . "hoge");
    $this->assertTrue($fs->isFile("test2" . DS . "foo2.txt"));
    $this->assertFalse($fs->isFile("hoge" . DS . "foo2.txt"));
    
    $moved->moveTo("foo3.txt");
    $this->assertTrue($fs->isFile("test2" . DS . "foo3.txt"));
    $this->assertFalse($fs->isFile("test2" . DS . "foo2.txt"));
  }
  
  public function testRemoveFile()
  {
    $fs = new Sabel_Util_FileSystem($this->basedir . DS . "test");
    $fs->isFile("hoge.txt");
    $fs->getFile("hoge.txt")->remove();
    $this->assertFalse($fs->isFile("hoge.txt"));
    $this->assertFalse($fs->isFile($this->basedir . DS . "test" . DS . "hoge.txt"));
    $this->assertFalse(is_file($this->basedir . DS . "test" . DS . "hoge.txt"));
  }
  
  public function testRemoveDir()
  {
    $fs = new Sabel_Util_FileSystem($this->basedir . DS . "test" . DS . "hoge");
    $this->assertTrue(in_array("test2", $fs->ls(), true));
    $fs->cd("test2");
    $fs->rmdir();
    
    $fs->cd($this->basedir . DS . "test" . DS . "hoge");
    $this->assertFalse(in_array("test2", $fs->ls(), true));
  }
  
  public function testDirectoryCopy()
  {
    $fs = new Sabel_Util_FileSystem($this->basedir . DS . "test");
    $this->assertFalse($fs->isDir("moved"));
    
    $fs->copyTo(".." . DS . "moved");
    $fs->cd("..");
    $this->assertTrue($fs->isDir("moved"));
    $fs->cd("moved");
    $this->assertTrue($fs->isDir("hoge"));
    $fs->cd("hoge");
    $this->assertTrue($fs->isDir("fuga"));
    $this->assertTrue($fs->isDir("test"));
    $fs->cd("fuga");
    $this->assertTrue($fs->isFile("foo.txt"));
    $fs->cd(".." . DS . "test");
    $this->assertTrue($fs->isFile("foo.txt"));
  }
  
  public function testCleanup()
  {
    $fs = new Sabel_Util_FileSystem($this->basedir);
    $fs->rmdir("test");
    $fs->rmdir("moved");
  }
}
