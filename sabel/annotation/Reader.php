<?php

/**
 * Sabel_Annotation_Reader
 *
 * @category   Annotation
 * @package    org.sabel.annotation
 * @author     Mori Reo <mori.reo@sabel.jp>
 * @author     Ebine Yutaka <ebine.yutaka@sabel.jp>
 * @copyright  2004-2008 Mori Reo <mori.reo@sabel.jp>
 * @license    http://www.opensource.org/licenses/bsd-license.php  BSD License
 */
class Sabel_Annotation_Reader extends Sabel_Object
{
  protected static $instance = null;
  
  private function __construct()
  {
    
  }
  
  public static function create()
  {
    if (self::$instance === null) {
      self::$instance = new self();
    }
    
    return self::$instance;
  }
  
  public function readClassAnnotation($class)
  {
    $reflection = new ReflectionClass($class);
    return $this->process($reflection->getDocComment());
  }
  
  public function readMethodAnnotation($class, $method)
  {
    $reflection = new ReflectionMethod($class, $method);
    return $this->process($reflection->getDocComment());
  }
  
  public function readPropertyAnnotation($class, $property)
  {
    $reflection = new ReflectionProperty($class, $property);
    return $this->process($reflection->getDocComment());
  }
  
  public function process($comment)
  {
    $annotations = array();
    preg_match_all("/@(.+)/", $comment, $comments);
    if (empty($comments[1])) return $annotations;
    
    foreach ($comments[1] as $line) {
      list ($name, $values) = $this->extract(trim($line));
      $annotations[$name][] = $values;
    }
    
    return $annotations;
  }
  
  protected function extract($line)
  {
    if (($pos = strpos($line, " ")) === false) {
      return array($line, null);
    }
    
    $key = substr($line, 0, $pos);
    $values = ltrim(substr($line, $pos));
    
    $regex = '/(".+[^\\\\]")|(\'.+[^\\\\]\')|([^ ]+?)/U';
    preg_match_all($regex, $values, $matches);
    
    $annotValues = array();
    foreach ($matches as $index => $match) {
      if ($index === 0) continue;
      foreach ($match as $k => $v) {
        if ($v === "") continue;
        
        $quote = $v{0};
        if (($quote === '"' || $quote === "'") && $quote === $v{strlen($v) - 1}) {
          $annotValues[$k] = substr(str_replace("\\{$quote}", $quote, $v), 1, -1);;
        } else {
          $annotValues[$k] = $v;
        }
      }
    }
    
    ksort($annotValues);
    
    return array($key, $annotValues);
  }
}
