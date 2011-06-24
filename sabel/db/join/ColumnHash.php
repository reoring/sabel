<?php

/**
 * Sabel_Db_Join_ColumnHash
 *
 * @category   DB
 * @package    org.sabel.db
 * @author     Ebine Yutaka <ebine.yutaka@sabel.jp>
 * @copyright  2004-2008 Mori Reo <mori.reo@sabel.jp>
 * @license    http://www.opensource.org/licenses/bsd-license.php  BSD License
 */
class Sabel_Db_Join_ColumnHash
{
  private static $columns = array();
  
  public static function toHash($as)
  {
    $hash = "a" . substr(md5($as), 0, 23);
    return self::$columns[$as] = $hash;
  }
  
  public static function getHash($as)
  {
    return (isset(self::$columns[$as])) ? self::$columns[$as] : $as;
  }
  
  public static function clear()
  {
    self::$columns = array();
  }
}
