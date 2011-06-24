<?php

/**
 * Sabel_Cookie_Factory
 *
 * @category   Cookie
 * @package    org.sabel.cookie
 * @author     Ebine Yutaka <ebine.yutaka@sabel.jp>
 * @copyright  2004-2008 Mori Reo <mori.reo@sabel.jp>
 * @license    http://www.opensource.org/licenses/bsd-license.php  BSD License
 */
class Sabel_Cookie_Factory
{
  public static function create()
  {
    if (is_cli()) {
      return Sabel_Cookie_InMemory::create();
    } else {
      return Sabel_Cookie_Http::create();
    }
  }
}
