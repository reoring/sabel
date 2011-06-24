<?php

/**
 * testcase for sabel.view.Renderer
 *
 * @category  View
 * @author    Ebine Yutaka <ebine.yutaka@sabel.jp>
 */
class Test_View_Renderer extends SabelTestCase
{
  public static function suite()
  {
    return self::createSuite("Test_View_Renderer");
  }
  
  public function testRenderingFromString()
  {
    $renderer = new Sabel_View_Renderer();
    $contents = 'name: <?php echo $name ?>';
    $result = $renderer->rendering($contents, array("name" => "hoge"));
    $this->assertEquals("name: hoge", $result);
  }
  
  public function testRenderingFromFile()
  {
    $renderer = new Sabel_View_Renderer();
    $path   = MODULES_DIR_PATH . "/views/test.tpl";
    $result = $renderer->rendering(null, array("a" => "10", "b" => "20"), $path);
    
    $expected = <<<CONTENTS
a: 10<br/>
b: 20
CONTENTS;
    
    $this->assertEquals($expected, rtrim($result));
  }
}
