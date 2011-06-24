<?php

/**
 * Sabel_Mail_Mime_Html
 *
 * @category   Mail
 * @package    org.sabel.mail
 * @author     Ebine Yutaka <ebine.yutaka@sabel.jp>
 * @copyright  2004-2008 Mori Reo <mori.reo@sabel.jp>
 * @license    http://www.opensource.org/licenses/bsd-license.php  BSD License
 */
class Sabel_Mail_Mime_Html extends Sabel_Mail_Mime_Abstract
{
  /**
   * @var array
   */
  protected $inlineImages = array();
  
  /**
   * @var string
   */
  protected $type = "text/html";
  
  public function __construct($content)
  {
    $this->content = $content;
  }
  
  /**
   * @param string $contentId unique id
   * @param array $image 'data' or 'path' are required.
   *   'data' => image data,
   *   'path' => image path,
   *   'mimetype' => mime type,
   *   'encoding' => encoding('base64'(default) or 'quoted-printable')
   *
   * @return self
   */
  public function addImage($contentId, $image)
  {
    if (is_array($image)) {
      foreach ($image as $k => $v) {
        if (($lk = strtolower($k)) !== $k) {
          $image[$lk] = $v;
          unset($image[$k]);
        }
      }
    } else {
      $image = array("path" => $image);
    }
    
    if (!isset($image["data"]) && !isset($image["path"])) {
      $message = __METHOD__ . "() must set arg2[data](image data) or arg2[path](image path).";
      throw new Sabel_Exception_InvalidArgument($message);
    }
    
    if (!isset($image["data"])) {
      if (($image["data"] = @file_get_contents($image["path"])) === false) {
        $message = __METHOD__ . "() failed to open stream: No such file or directory in '{$image['path']}'.";
        throw new Sabel_Exception_FileNotFound($message);
      }
    }
    
    if (!isset($image["encoding"])) {
      $image["encoding"] = "base64";
    }
    
    if (!isset($image["mimetype"])) {
      $mimetype = get_mime_type($image["data"]);
      $image["mimetype"] = (!$mimetype) ? "application/octet-stream" : $mimetype;
    }
    
    $this->inlineImages[] = array(
      "cid"      => $contentId,
      "data"     => $image["data"],
      "mimetype" => $image["mimetype"],
      "encoding" => $image["encoding"]
    );
    
    return $this;
  }
  
  public function getImages()
  {
    return $this->inlineImages;
  }
  
  public function hasImage()
  {
    return !empty($this->inlineImages);
  }
  
  /**
   * @return string
   */
  public function toMailPart($boundary = null)
  {
    if ($this->hasImage() && $boundary === null) {
      $message = __METHOD__ . "() Because the inline image exists, boundary is necessary.";
      throw new Sabel_Mail_Exception($message);
    }
    
    $part = array();
    $eol  = Sabel_Mail::getEol();
    
    $part[] = "Content-Disposition: " . $this->disposition;
    $part[] = "Content-Transfer-Encoding: " . $this->encoding;
    $part[] = "Content-Type: {$this->type}; charset=" . $this->charset . $eol;
    $part[] = $this->getEncodedContent() . $eol;
    
    if ($this->hasImage()) {
      foreach ($this->inlineImages as $image) {
        $enc    = $image["encoding"];
        $part[] = "--{$boundary}";
        $part[] = "Content-Type: {$image["mimetype"]}";
        $part[] = "Content-Transfer-Encoding: $enc";
        $part[] = "Content-ID: <{$image["cid"]}>";
        $part[] = $eol . $this->encode($image["data"], $enc, $eol) . $eol;
      }
    }
    
    return implode($eol, $part);
  }
}
