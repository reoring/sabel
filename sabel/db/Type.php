<?php

/**
 * Sabel_Db_Type
 *
 * @interface
 * @category   DB
 * @package    org.sabel.db
 * @author     Ebine Yutaka <ebine.yutaka@sabel.jp>
 * @copyright  2004-2008 Mori Reo <mori.reo@sabel.jp>
 * @license    http://www.opensource.org/licenses/bsd-license.php  BSD License
 */
interface Sabel_Db_Type
{
  const INT       = "_INT";
  const BIGINT    = "_BIGINT";
  const SMALLINT  = "_SMALLINT";
  const FLOAT     = "_FLOAT";
  const DOUBLE    = "_DOUBLE";
  const STRING    = "_STRING";
  const TEXT      = "_TEXT";
  const BOOL      = "_BOOL";
  const DATETIME  = "_DATETIME";
  const DATE      = "_DATE";
  const BINARY    = "_BINARY";
  const UNKNOWN   = "_UNKNOWN";
}
