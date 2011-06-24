<?php

/**
 * Sabel Preference backend interface.
 *
 * this interface must be implement backend driver class.
 *
 * @abstract
 * @category   Preference
 * @package    org.sabel.preference
 * @author     Mori Reo <mori.reo@sabel.jp>
 * @copyright  2004-2008 Mori Reo <mori.reo@sabel.jp>
 * @license    http://www.opensource.org/licenses/bsd-license.php  BSD License
 */
interface Sabel_Preference_Backend
{
  public function set($key, $value, $type);
  public function has($key);
  public function get($key);
  public function getAll();
  public function delete($key);
}