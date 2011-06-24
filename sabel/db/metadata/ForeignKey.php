<?php

/**
 * Sabel_Db_Metadata_ForeignKey
 *
 * @category   DB
 * @package    org.sabel.db
 * @author     Ebine Yutaka <ebine.yutaka@sabel.jp>
 * @copyright  2004-2008 Mori Reo <mori.reo@sabel.jp>
 * @license    http://www.opensource.org/licenses/bsd-license.php  BSD License
 */
class Sabel_Db_Metadata_ForeignKey extends Sabel_Object
{
  /**
   * @var array
   */
  private $fkeys = array();
  
  /**
   * @var array
   */
  private $objects = array();
  
  public function __construct($fkeys)
  {
    if (is_array($fkeys)) {
      $this->fkeys = $fkeys;
    } else {
      $message = __METHOD__ . "() argument must be an array.";
      throw new Sabel_Exception_InvalidArgument($message);
    }
  }
  
  public function has($key)
  {
    return isset($this->fkeys[$key]);
  }
  
  public function __get($key)
  {
    if ($this->has($key)) {
      if (isset($this->objects[$key])) {
        return $this->objects[$key];
      } else {
        $fkey = $this->fkeys[$key];
        $stdClass = new stdClass();
        $stdClass->table     = $fkey["referenced_table"];
        $stdClass->column    = $fkey["referenced_column"];
        $stdClass->onDelete  = $fkey["on_delete"];
        $stdClass->onUpdate  = $fkey["on_update"];
        
        return $this->objects[$key] = $stdClass;
      }
    } else {
      return null;
    }
  }
  
  public function toArray()
  {
    $fkeys = array();
    foreach (array_keys($this->fkeys) as $key) {
      $fkeys[$key] = $this->__get($key);
    }
    
    return $fkeys;
  }
}
