<?php

if (!defined("MODELS_DIR_PATH")) {
  define("MODELS_DIR_PATH", ".");
}

/**
 * preference store to a database
 *
 * @abstract
 * @category   Preference
 * @package    org.sabel.preference
 * @author     Mori Reo <mori.reo@sabel.jp>
 * @copyright  2004-2008 Mori Reo <mori.reo@sabel.jp>
 * @license    http://www.opensource.org/licenses/bsd-license.php  BSD License
 */
class Test_Preference_Database extends Test_Preference_Base
{
  public function __construct()
  {
    Sabel_Db_Config::initialize(new Config_Database());
  }
  
  public static function suite()
  {
    return self::createSuite("Test_Preference_Database");
  }

  /**
   * set up
   *
   * @access public
   * @return void
   */
  public function setUp()
  {
    $this->pref = Sabel_Preference::create(new __Preference_Database_Config());
  }
  
  public function tearDown()
  {
    MODEL("SblPreference")->prepareStatement(Sabel_Db_Statement::DELETE)->execute();
  }
  
  public function testUseNamespace()
  {
    $pref = Sabel_Preference::create(new __Preference_Database_Config_Namespace());
    $pref->setInt("test", 1);
    
    $this->assertEquals(1, MODEL("SblPreference")->getCount("namespace", "myapp"));
  }
  
  public function testUseCustomModelClass()
  {
    $pref = Sabel_Preference::create(new __Preference_Database_Config_CustomModel());
    $pref->setInt("test", 1);
  }
}

class __Preference_Database_Config implements Sabel_Config
{
  public function configure()
  {
    return array("backend" => "Sabel_Preference_Database");
  }
}

class __Preference_Database_Config_Namespace implements Sabel_Config
{
  public function configure()
  {
    return array("backend"   => "Sabel_Preference_Database",
                 "namespace" => "myapp");
  }
}

class __Preference_Database_Config_CustomModel implements Sabel_Config
{
  public function configure()
  {
    return array("backend"   => "Sabel_Preference_Database",
                 "model" => "__Preference_CustomModel");
  }
}

class __Preference_CustomModel extends Sabel_Db_Model
{
  protected $tableName = "sbl_preference";
}

class Config_Database implements Sabel_Config
{
  public function configure()
  {
    switch (ENVIRONMENT) {
      case PRODUCTION:
        $params = array("default" => array(
                          "package"  => "sabel.db.*",
                          "host"     => "localhost",
                          "database" => "dbname",
                          "user"     => "user",
                          "password" => "password")
                       );
        break;
        
      case TEST:
        $params = array("default" => array(
                          "package"  => "sabel.db.mysql",
                          "host"     => "localhost",
                          "database" => "sabel",
                          "user"     => "root",
                          "password" => "")
                       );
        break;
        
      case DEVELOPMENT:
        $params = array("default" => array(
                          "package"  => "sabel.db.mysql",
                          "host"     => "localhost",
                          "database" => "sabel",
                          "user"     => "root",
                          "password" => "")
                       );
        break;
    }
    
    return $params;
  }
}