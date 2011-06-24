<?php

/**
 * test case for sabel.xml.*
 *
 * @category  XML
 * @author    Ebine Yutaka <ebine.yutaka@sabel.jp>
 */
class Test_XML_Test extends SabelTestCase
{
  public static function suite()
  {
    return self::createSuite("Test_XML_Test");
  }
  
  /**
   * @test
   */
  public function initialize()
  {
    $this->outputUsersXml();
  }
  
  public function testDocument()
  {
    $xml = Sabel_Xml_Document::create();
    $test = $this->loadXML($xml, "simple");
    $this->assertEquals("test", $test->tagName);
    $this->assertEquals("utf-8", $xml->getEncoding());
    $this->assertEquals("1.0", $xml->getVersion());
  }
  
  public function testAttribute()
  {
    $xml = Sabel_Xml_Document::create();
    $test = $this->loadXML($xml, "simple");
    $this->assertEquals("foo", $test->getChild("foo")->at("attr"));
    $this->assertEquals("bar", $test->getChild("bar")->at("attr"));
    $this->assertEquals("baz", $test->getChild("baz")->at("attr"));
  }
  
  public function testAttributes()
  {
    $xml = Sabel_Xml_Document::create();
    $test = $this->loadXML($xml, "simple");
    $attrs = $test->getChild("foo")->getAttributes();
    
    $this->assertEquals(true,  $attrs->has("a"));
    $this->assertEquals(true,  $attrs->has("b"));
    $this->assertEquals(true,  $attrs->has("c"));
    $this->assertEquals(false, $attrs->has("d"));
    
    $this->assertEquals("10", $attrs->get("a"));
    $this->assertEquals("20", $attrs->get("b"));
    $this->assertEquals("30", $attrs->get("c"));
    $this->assertEquals(null, $attrs->get("d"));
    
    // getter
    
    $this->assertEquals("10", $attrs->a);
    $this->assertEquals("20", $attrs->b);
    $this->assertEquals("30", $attrs->c);
    $this->assertEquals(null, $attrs->d);
  }
  
  public function testNodeValue()
  {
    $xml = Sabel_Xml_Document::create();
    $test = $this->loadXML($xml, "simple");
    $this->assertEquals("footext", trim($test->getChild("foo")->getValue()));
    $this->assertEquals("bartext", trim($test->getChild("bar")->getValue()));
    $this->assertEquals("baztext", trim($test->getChild("baz")->getValue()));
  }
  
  public function testElementsCount()
  {
    $xml = Sabel_Xml_Document::create();
    $test = $this->loadXML($xml, "test");
    $this->assertEquals(2, $test->getRawElement()->getElementsByTagName("foo")->length);
    $this->assertEquals(1, $test->getChildren("foo")->length);
  }
  
  public function testCreateDocument()
  {
    $xml = Sabel_Xml_Document::create();
    $xml->setEncoding("utf-8")->setVersion("1.0");
    
    $users = $xml->createElement("users");
    
    $aUser = $users->addChild("user");
    $aUser->addChild("name", "tanaka");
    $aUser->addChild("age", "18");
    
    $aUser = $users->addChild("user");
    $aUser->addChild("name", "suzuki");
    $aUser->addChild("age", "25");
    
    $aUser = $users->addChild("user");
    $aUser->addChild("name", "satou");
    $aUser->addChild("age", "40");
    
    $xml->setDocumentElement($users);
    $this->saveXML($xml, "users");
  }
  
  public function testElements()
  {
    $xml = Sabel_Xml_Document::create();
    $_users = $this->loadXML($xml, "users");
    $users = $_users->getChildren("user");
    $this->assertEquals(3, $users->length);
    
    foreach ($users as $i => $user) {}
    $this->assertEquals(2, $i);
    
    $this->assertEquals("tanaka", $users[0]->getChild("name")->getValue());
    $this->assertEquals("18",     $users[0]->getChild("age")->getValue());
    $this->assertEquals("suzuki", $users[1]->getChild("name")->getValue());
    $this->assertEquals("25",     $users[1]->getChild("age")->getValue());
    $this->assertEquals("satou",  $users[2]->getChild("name")->getValue());
    $this->assertEquals("40",     $users[2]->getChild("age")->getValue());
  }
  
  public function testSimpleAccess()
  {
    $xml = Sabel_Xml_Document::create();
    $users = $this->loadXML($xml, "users");
    
    $this->assertEquals("tanaka", $users->user[0]->name[0]->getValue());
    $this->assertEquals("18",     $users->user[0]->age[0]->getValue());
    $this->assertEquals("suzuki", $users->user[1]->name[0]->getValue());
    $this->assertEquals("25",     $users->user[1]->age[0]->getValue());
    $this->assertEquals("satou",  $users->user[2]->name[0]->getValue());
    $this->assertEquals("40",     $users->user[2]->age[0]->getValue());
  }
  
  public function setSetNodeValue()
  {
    $xml = Sabel_Xml_Document::create();
    $users = $this->loadXML($xml, "users");
    $users->user[0]->age[0]->setNodeValue("20");
    $this->saveXML($xml, "users");
    
    $xml = new Sabel_Xml_Document();
    $users = $this->loadXML($xml, "users");
    $this->assertEquals("20", $users->user[0]->age[0]->getValue());
  }
  
  public function testSetAttribute()
  {
    $xml = Sabel_Xml_Document::create();
    $users = $this->loadXML($xml, "users");
    $users->user[0]->setAttribute("id", 1);
    $users->user[1]->setAttribute("id", 2);
    $users->user[2]->setAttribute("id", 3);
    $this->saveXML($xml, "users");
    
    $xml = Sabel_Xml_Document::create();
    $users = $this->loadXML($xml, "users");
    $this->assertEquals("1", $users->user[0]->getAttribute("id"));
    $this->assertEquals("2", $users->user[1]->getAttribute("id"));
    $this->assertEquals("3", $users->user[2]->getAttribute("id"));
    
    $this->assertEquals("1", $users->user[0]->at("id"));
    $this->assertEquals("2", $users->user[1]->at("id"));
    $this->assertEquals("3", $users->user[2]->at("id"));
  }
  
  public function testInsertBefore()
  {
    $xml = Sabel_Xml_Document::create();
    $users = $this->loadXML($xml, "users");
    
    $this->assertEquals("tanaka", $users->user[0]->name[0]->getValue());
    $this->assertEquals("suzuki", $users->user[1]->name[0]->getValue());
    $this->assertEquals("satou",  $users->user[2]->name[0]->getValue());
    
    $aUser = $xml->createElement("user");
    $aUser->addChild("name", "yamada");
    $aUser->addChild("age", "60");
    
    $users->user[2]->insertPreviousSibling($aUser);
    $this->saveXML($xml, "users");
    
    //-------------------------------------
    
    $xml = Sabel_Xml_Document::create();
    $users = $this->loadXML($xml, "users");
    $this->assertEquals("tanaka", $users->user[0]->name[0]->getValue());
    $this->assertEquals("suzuki", $users->user[1]->name[0]->getValue());
    $this->assertEquals("yamada", $users->user[2]->name[0]->getValue());
    $this->assertEquals("satou",  $users->user[3]->name[0]->getValue());
  }
  
  public function testInsertAfter()
  {
    $xml = Sabel_Xml_Document::create();
    $users = $this->loadXML($xml, "users");
    
    $aUser = $xml->createElement("user");
    $aUser->addChild("name", "koike");
    $aUser->addChild("age", "80");
    
    $users->user[3]->insertNextSibling($aUser);
    $this->saveXML($xml, "users");
    
    //-------------------------------------
    
    $xml = Sabel_Xml_Document::create();
    $users = $this->loadXML($xml, "users");
    $this->assertEquals("tanaka", $users->user[0]->name[0]->getValue());
    $this->assertEquals("suzuki", $users->user[1]->name[0]->getValue());
    $this->assertEquals("yamada", $users->user[2]->name[0]->getValue());
    $this->assertEquals("satou",  $users->user[3]->name[0]->getValue());
    $this->assertEquals("koike",  $users->user[4]->name[0]->getValue());
  }
  
  public function testGetParent()
  {
    $xml = Sabel_Xml_Document::create();
    $users = $this->loadXML($xml, "users");
    
    $age = $users->user[0]->age[0];
    $this->assertEquals("age", $age->tagName);
    $this->assertEquals("user", $age->getParent()->tagName);
    $this->assertEquals("users", $age->getParent("users")->tagName);
  }
  
  public function testGetFirstChild()
  {
    $xml = Sabel_Xml_Document::create();
    $users = $this->loadXML($xml, "users");
    
    $aUser = $users->user[0];
    $this->assertEquals("name", $aUser->getFirstChild()->tagName);
  }
  
  public function testGetLastChild()
  {
    $xml = Sabel_Xml_Document::create();
    $users = $this->loadXML($xml, "users");
    
    $aUser = $users->getLastChild();
    $this->assertEquals("user", $aUser->tagName);
    $this->assertEquals("age", $aUser->getLastChild()->tagName);
    $this->assertEquals("80", $aUser->getLastChild()->getValue());
  }
  
  public function testGetNextSibling()
  {
    $xml = Sabel_Xml_Document::create();
    $users = $this->loadXML($xml, "users");
    
    $aUser = $users->getFirstChild();
    $this->assertEquals("tanaka", $aUser->name[0]->getValue());
    $aUser = $aUser->getNextSibling();
    $this->assertEquals("suzuki", $aUser->name[0]->getValue());
    $aUser = $aUser->getNextSibling();
    $this->assertEquals("yamada", $aUser->name[0]->getValue());
    $aUser = $aUser->getNextSibling();
    $this->assertEquals("satou", $aUser->name[0]->getValue());
    $aUser = $aUser->getNextSibling();
    $this->assertEquals("koike", $aUser->name[0]->getValue());
    $aUser = $aUser->getNextSibling();
    $this->assertEquals(null, $aUser);
  }
  
  public function testGetNextSiblings()
  {
    $xml = Sabel_Xml_Document::create();
    $users = $this->loadXML($xml, "users");
    
    $elems = $users->user[2]->getNextSiblings();
    $this->assertEquals(2, $elems->length);
    $this->assertEquals("satou", $elems[0]->name[0]->getValue());
    $this->assertEquals("koike", $elems[1]->name[0]->getValue());
  }
  
  public function testGetPreviousSibling()
  {
    $xml = Sabel_Xml_Document::create();
    $users = $this->loadXML($xml, "users");
    
    $aUser = $users->getLastChild();
    $this->assertEquals("koike", $aUser->name[0]->getValue());
    $aUser = $aUser->getPreviousSibling();
    $this->assertEquals("satou", $aUser->name[0]->getValue());
    $aUser = $aUser->getPreviousSibling();
    $this->assertEquals("yamada", $aUser->name[0]->getValue());
    $aUser = $aUser->getPreviousSibling();
    $this->assertEquals("suzuki", $aUser->name[0]->getValue());
    $aUser = $aUser->getPreviousSibling();
    
    $this->assertEquals("tanaka", $aUser->name[0]->getValue());
    $aUser = $aUser->getPreviousSibling();
    $this->assertEquals(null, $aUser);
  }
  
  public function testGetPreviousSiblings()
  {
    $xml = Sabel_Xml_Document::create();
    $users = $this->loadXML($xml, "users");
    
    $elems = $users->user[2]->getPreviousSiblings();
    $this->assertEquals(2, $elems->length);
    $this->assertEquals("suzuki", $elems[0]->name[0]->getValue());
    $this->assertEquals("tanaka", $elems[1]->name[0]->getValue());
    
    $elems->reverse();
    
    $this->assertEquals("tanaka", $elems[0]->name[0]->getValue());
    $this->assertEquals("suzuki", $elems[1]->name[0]->getValue());
  }
  
  public function testGetSiblings()
  {
    $xml = Sabel_Xml_Document::create();
    $users = $this->loadXML($xml, "users");
    
    $elems = $users->user[2]->getSiblings();
    $this->assertEquals(4, $elems->length);
    $this->assertEquals("tanaka", $elems[0]->name[0]->getValue());
    $this->assertEquals("suzuki", $elems[1]->name[0]->getValue());
    $this->assertEquals("satou",  $elems[2]->name[0]->getValue());
    $this->assertEquals("koike",  $elems[3]->name[0]->getValue());
  }
  
  public function testFindFromAttribute()
  {
    $xml = Sabel_Xml_Document::create();
    $users = $this->loadXML($xml, "find");
    
    $elems = $users->select("from user where @id = 1");
    $this->assertEquals(1, $elems->length);
    
    $elem = $elems[0];
    $this->assertEquals("1", $elem->at("id"));
    $this->assertEquals("tanaka", $elem->profile[0]->name[0]->getValue());
    
    $elems = $users->select("from user.foo.bar where @type = 'b'");
    $this->assertEquals(1, $elems->length);
    
    $elem = $elems[0]->getParent("user");
    $this->assertEquals("2", $elem->at("id"));
    $this->assertEquals("suzuki", $elem->profile[0]->name[0]->getValue());
    
    // not equals
    $elems = $users->select("from user where not @id = 1");
    $this->assertEquals(4, $elems->length);
  }
  
  public function testSelectByIsNull()
  {
    $xml = Sabel_Xml_Document::create();
    $users = $this->loadXML($xml, "find");
    
    $elems = $users->select("from user.foo.bar where @type IS NULL");
    $this->assertEquals(1, $elems->length);
    
    $elem = $elems[0]->getParent("user");
    $this->assertEquals("5", $elem->at("id"));
    $this->assertEquals("koike", $elem->profile[0]->name[0]->getValue());
    
    $elems = $users->select("from user.foo.bar where @type IS NOT NULL");
    $this->assertEquals(4, $elems->length);
    
    $elems = $users->select("from user where test IS NULL");
    $this->assertEquals(3, $elems->length);
    
    $elems = $users->select("from user where test IS NOT NULL");
    $this->assertEquals(2, $elems->length);
  }
  
  public function testReturnElement()
  {
    $xml = Sabel_Xml_Document::create();
    $users = $this->loadXML($xml, "find");
    
    $elems = $users->select("from user.foo.bar where @type = 'b'");
    $this->assertEquals("bar", $elems[0]->tagName);
    
    $elems = $users->select("from user.foo.bar where @type IS NULL");
    $this->assertEquals("bar", $elems[0]->tagName);
    
    $elems = $users->select("from user.foo.bar where @type IS NOT NULL");
    $this->assertEquals("bar", $elems[0]->tagName);
    
    //-------------------------------------------
    
    $elems = $users->select("from user where foo.bar@type = 'b'");
    $this->assertEquals("user", $elems[0]->tagName);
    
    $elems = $users->select("from user where foo.bar@type IS NULL");
    $this->assertEquals("user", $elems[0]->tagName);
    
    $elems = $users->select("from user where foo.bar@type IS NOT NULL");
    $this->assertEquals("user", $elems[0]->tagName);
    
    //-------------------------------------------
    
    $aUser = $users->user[0];
    $elems = $aUser->select("from . where @id = 2");
    $this->assertEquals("2", $elems[0]->at("id"));
    $this->assertEquals("suzuki", $elems[0]->profile[0]->name[0]->getValue());
    
    //-------------------------------------------
    
    $elems = $users->select("from user.foo.bar.baz where value() = 'test456'");
    $this->assertEquals("baz", $elems[0]->tagName);
    $this->assertEquals("test456", $elems[0]->getValue());
    $this->assertEquals("2", $elems[0]->getParent("user")->at("id"));
  }
  
  public function testLike()
  {
    $xml = Sabel_Xml_Document::create();
    $users = $this->loadXML($xml, "find");
    
    $elems = $users->select("from user where foo.bar.baz like 'test%'");
    $this->assertEquals(2, $elems->length);
    $this->assertEquals("tanaka", $elems[0]->profile[0]->name[0]->getValue());
    $this->assertEquals("suzuki", $elems[1]->profile[0]->name[0]->getValue());
    
    $elems = $users->select("from user where foo.bar.baz like '%456%'");
    $this->assertEquals(3, $elems->length);
    $this->assertEquals("suzuki", $elems[0]->profile[0]->name[0]->getValue());
    $this->assertEquals("satou",  $elems[1]->profile[0]->name[0]->getValue());
    $this->assertEquals("koike",  $elems[2]->profile[0]->name[0]->getValue());
    
    //-------------------------------------------------------
    
    $elems = $users->select("from user where not foo.bar.baz like 'test%'");
    $this->assertEquals(3, $elems->length);
    $this->assertEquals("yamada", $elems[0]->profile[0]->name[0]->getValue());
    $this->assertEquals("satou",  $elems[1]->profile[0]->name[0]->getValue());
    $this->assertEquals("koike",  $elems[2]->profile[0]->name[0]->getValue());
    
    $elems = $users->select("from user where not foo.bar.baz like '%456%'");
    $this->assertEquals(2, $elems->length);
    $this->assertEquals("tanaka", $elems[0]->profile[0]->name[0]->getValue());
    $this->assertEquals("yamada", $elems[1]->profile[0]->name[0]->getValue());
  }
  
  public function testAnd()
  {
    $xml = Sabel_Xml_Document::create();
    $users = $this->loadXML($xml, "find");
    
    $elems = $users->select("from user where foo.bar.baz LIKE '%456%' AND test IS NOT NULL");
    $this->assertEquals(1, $elems->length);
    $this->assertEquals("koike", $elems[0]->profile[0]->name[0]->getValue());
  }
  
  public function testOr()
  {
    $xml = Sabel_Xml_Document::create();
    $users = $this->loadXML($xml, "find");
    
    $elems = $users->select("from user where profile.age >= 60 OR profile.age <= 20");
    $this->assertEquals(3, $elems->length);
    $this->assertEquals("tanaka", $elems[0]->profile[0]->name[0]->getValue());
    $this->assertEquals("1",      $elems[0]->at("id"));
    $this->assertEquals("yamada", $elems[1]->profile[0]->name[0]->getValue());
    $this->assertEquals("3",      $elems[1]->at("id"));
    $this->assertEquals("koike",  $elems[2]->profile[0]->name[0]->getValue());
    $this->assertEquals("5",      $elems[2]->at("id"));
  }
  
  public function testSwap()
  {
    $xml = Sabel_Xml_Document::create();
    $users = $this->loadXML($xml, "users");
    
    $tanaka = $users->user[0];
    $satou  = $users->user[3];
    $this->assertEquals("tanaka", $tanaka->name[0]->getValue());
    $this->assertEquals("satou",  $satou->name[0]->getValue());
    
    $tanaka->swap($satou);
    
    $tanaka = $users->user[3];
    $satou  = $users->user[0];
    $this->assertEquals("tanaka", $tanaka->name[0]->getValue());
    $this->assertEquals("satou",  $satou->name[0]->getValue());
    
    $suzuki = $users->user[1];
    $koike  = $users->user[4];
    $this->assertEquals("suzuki", $suzuki->name[0]->getValue());
    $this->assertEquals("koike",  $koike->name[0]->getValue());
    
    $koike->swap($suzuki);
    
    $suzuki = $users->user[4];
    $koike  = $users->user[1];
    $this->assertEquals("suzuki", $suzuki->name[0]->getValue());
    $this->assertEquals("koike",  $koike->name[0]->getValue());
    
    $koike  = $users->user[1];
    $yamada = $users->user[2];
    $this->assertEquals("koike",  $koike->name[0]->getValue());
    $this->assertEquals("yamada", $yamada->name[0]->getValue());
    
    $koike->swap($yamada);
    
    $koike  = $users->user[2];
    $yamada = $users->user[1];
    $this->assertEquals("koike",  $koike->name[0]->getValue());
    $this->assertEquals("yamada", $yamada->name[0]->getValue());
  }
  
  public function testRemoveElement()
  {
    $xml = Sabel_Xml_Document::create();
    $users = $this->loadXML($xml, "users");
    
    $this->assertEquals(5, $users->user->length);
    
    $deleted = $users->user[2]->remove();
    $this->assertEquals("yamada", $deleted->name[0]->getValue());
    $this->assertEquals(4, $users->user->length);
    
    $deleted = $users->user[2]->remove();
    $this->assertEquals("satou", $deleted->name[0]->getValue());
    $this->assertEquals(3, $users->user->length);
    
    $this->saveXML($xml, "users");
  }
  
  public function testDelete()
  {
    $xml = Sabel_Xml_Document::create();
    $users = $this->loadXML($xml, "users");
    
    $aUser = $users->user[1];
    $this->assertEquals(1, $aUser->age->length);
    $this->assertEquals("25", $aUser->age[0]->getValue());
    
    $users->delete("from user.age where value() = 25");
    
    $this->assertEquals(0, $aUser->age->length);
    
    //-------------------------------------------------
   
    $this->assertEquals(3, $users->user->length);
    
    $users->delete("from user where age = 18");
    
    $this->assertEquals(2, $users->user->length);
    
    $this->saveXML($xml, "users");
  }
  
  public function testCDATA()
  {
    $xml = Sabel_Xml_Document::create();
    $users = $this->loadXML($xml, "users");
    $aUser = $users->getFirstChild();
    $aUser->name[0]->setValue(null);
    
    $cdata = $xml->createCDATA("<test><![CDATA['test']]></test>");
    $aUser->name[0]->appendChild($cdata);
    $this->saveXML($xml, "users");
    
    //--------------------------------------------
    
    $xml = Sabel_Xml_Document::create();
    $users = $this->loadXML($xml, "users");
    $aUser = $users->getFirstChild();
    $this->assertEquals("<test><![CDATA['test']]></test>", $aUser->name[0]->getValue());
  }
  
  public function testNamespace()
  {
    $xml = Sabel_Xml_Document::create();
    $nodes = $this->loadXML($xml, "ns");
    
    $this->assertEquals("defitem4 value", $nodes->defitem3[0]->defitem4[0]->getValue());
    
    $foo = $nodes->getChild("foo:foo");
    $this->assertEquals("fooitem2 value", $foo->fooitem1[0]->fooitem2[0]->getValue());
    
    $defitem = $foo->getChild("defitem", "http://www.example.com/default");
    $this->assertEquals("defitem2 value", $defitem->defitem2[0]->getValue());
    
    $baz = $foo->getChild("baz", "http://www.example.com/baz");
    $this->assertEquals("bazitem3 value", $baz->bazitem1[0]->bazitem2[0]->bazitem3[0]->getValue());
  }
  
  public function testGetAllChildren()
  {
    $xml = Sabel_Xml_Document::create();
    $nodes = $this->loadXML($xml, "ns");
    $foo = $nodes->getChild("foo:foo");
    
    $children = $foo->getChildren();
    $this->assertEquals(4, $children->length);
    $this->assertEquals("foo:fooitem1", $children->item(0)->tagName);
    $this->assertEquals("defitem",      $children->item(1)->tagName);
    $this->assertEquals("bar:baritem1", $children->item(2)->tagName);
    $this->assertEquals("baz",          $children->item(3)->tagName);
  }
  
  protected function getXmlAsString($name)
  {
    return file_get_contents(XML_TEST_DIR . DS . "xml" . DS . $name . ".xml");
  }
  
  protected function loadXML(Sabel_Xml_Document $xml, $name)
  {
    return $xml->loadXML(file_get_contents(XML_TEST_DIR . DS . "xml" . DS . $name . ".xml"));
  }
  
  protected function saveXML(Sabel_Xml_Document $xml, $name = "_tmp")
  {
    return $xml->saveXML(XML_TEST_DIR . DS . "xml" . DS . $name . ".xml");
  }
  
  protected function outputUsersXml()
  {
    $xml = <<<XML
<?xml version="1.0" encoding="utf-8"?>
<users>
  <user>
    <name>tanaka</name>
    <age>18</age>
  </user>
  <user>
    <name>suzuki</name>
    <age>25</age>
  </user>
  <user>
    <name>satou</name>
    <age>40</age>
  </user>
</users>
XML;
    
    file_put_contents(XML_TEST_DIR . DS . "xml" . DS . "users.xml", $xml);
  }
}
