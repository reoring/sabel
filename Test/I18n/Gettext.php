<?php

/**
 * @category  I18n
 * @author    Ebine Yutaka <ebine.yutaka@sabel.jp>
 */
class Test_I18n_Gettext extends SabelTestCase
{
  public static function suite()
  {
    ini_set("mbstring.internal_encoding", "UTF-8");
    define("LOCALE_DIR_PATH", dirname(__FILE__) . DIRECTORY_SEPARATOR . "locale");
    return self::createSuite("Test_I18n_Gettext");
  }
  
  public function testI18n()
  {
    $gettext = Sabel_I18n_Gettext::getInstance();
    $gettext->setMessagesFileName("messages.php");
    $gettext->init("ja,en-us;q=0.7,en;q=0.3");
    
    $this->assertTrue($gettext->isInitialized());
    
    $this->assertEquals("名前", __("name"));
    $this->assertEquals("住所", __("address"));
  }
  
  public function testMessagesFileName()
  {
    $this->assertEquals("名前", __("name"));
    $this->assertEquals("住所", __("address"));
    
    Sabel_I18n_Gettext::getInstance()->setMessagesFileName("hiragana.php");
    $this->assertEquals("なまえ", __("name"));
    $this->assertEquals("じゅうしょ", __("address"));
  }
  
  public function testCodeSet()
  {
    $gettext = Sabel_I18n_Gettext::getInstance();
    $gettext->setCodeSet("EUC-JP");
    
    $this->assertEquals(mb_convert_encoding("なまえ", "EUC-JP", "UTF-8"), __("name"));
    $this->assertEquals(mb_convert_encoding("じゅうしょ", "EUC-JP", "UTF-8"), __("address"));
  }
}

function __($msgid)
{
  return Sabel_I18n_Sabel_Gettext::_($msgid);
}
