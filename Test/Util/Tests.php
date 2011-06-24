<?php

require_once ("Test/Util/String.php");
require_once ("Test/Util/Map.php");
require_once ("Test/Util/List.php");
require_once ("Test/Util/HashList.php");
require_once ("Test/Util/FileSystem.php");

/**
 * @category  Util
 * @author    Ebine Yutaka <ebine.yutaka@sabel.jp>
 */
class Test_Util_Tests
{
  public static function suite()
  {
    $suite = new PHPUnit_Framework_TestSuite();
    $suite->addTest(Test_Util_String::suite());
    $suite->addTest(Test_Util_Map::suite());
    $suite->addTest(Test_Util_LinkedList::suite());
    $suite->addTest(Test_Util_HashList::suite());
    $suite->addTest(Test_Util_FileSystem::suite());
    
    return $suite;
  }
}
