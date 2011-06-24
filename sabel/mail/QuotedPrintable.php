<?php

/**
 * Sabel_Mail_QuotedPritable
 *
 * @category   Mail
 * @package    org.sabel.mail
 * @author     Ebine Yutaka <ebine.yutaka@sabel.jp>
 * @copyright  2004-2008 Mori Reo <mori.reo@sabel.jp>
 * @license    http://www.opensource.org/licenses/bsd-license.php  BSD License
 */
class Sabel_Mail_QuotedPrintable
{
  public static function encode($str, $lineLength = 74, $lineEnd = "\r\n")
  {
    $fp = fopen("php://temp", "r+");
    
    stream_filter_append(
      $fp,
      "convert.quoted-printable-encode",
      STREAM_FILTER_READ,
      array(
        "line-length"      => $lineLength,
        "line-break-chars" => $lineEnd
      )
    );
    
    fputs($fp, $str);
    rewind($fp);
    $encoded = str_replace("_", "=5F", stream_get_contents($fp));
    fclose($fp);
    
    return $encoded;
  }
  
  public static function decode($str, $isHeader = true)
  {
    if ($isHeader) {
      $str = str_replace("_", " ", $str);
    }
    
    return quoted_printable_decode($str);
  }
}
