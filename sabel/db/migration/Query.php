<?php

/**
 * Sabel_Db_Migration_Query
 *
 * @category   DB
 * @package    org.sabel.db
 * @author     Ebine Yutaka <ebine.yutaka@sabel.jp>
 * @copyright  2004-2008 Mori Reo <mori.reo@sabel.jp>
 * @license    http://www.opensource.org/licenses/bsd-license.php  BSD License
 */
class Sabel_Db_Migration_Query
{
  private
    $upgradeQueries   = array(),
    $downgradeQueries = array();
    
  public function upgrade($query)
  {
    if (is_string($query)) {
      $this->upgradeQueries[] = $query;
    } else {
      Sabel_Console::error("argument must be a string.");
      exit;
    }
  }
  
  public function downgrade($query)
  {
    if (is_string($query)) {
      $this->downgradeQueries[] = $query;
    } else {
      Sabel_Console::error("argument must be a string.");
      exit;
    }
  }
  
  public function execute()
  {
    if (Sabel_Db_Migration_Manager::isUpgrade()) {
      $queries = $this->upgradeQueries;
    } else {
      $queries = $this->downgradeQueries;
    }
    
    $stmt = Sabel_Db_Migration_Manager::getStatement();
    foreach ($queries as $query) $stmt->setQuery($query)->execute();
  }
}
