<?php

/**
 * Sabel_Db_Migration_Reader
 *
 * @category   DB
 * @package    org.sabel.db
 * @author     Ebine Yutaka <yutaka@ebine.org>
 * @copyright  2004-2008 Mori Reo <mori.reo@sabel.jp>
 * @license    http://www.opensource.org/licenses/bsd-license.php  BSD License
 */
class Sabel_Db_Migration_Reader extends Sabel_Object
{
  private $filePath = "";
  
  public function __construct($filePath)
  {
    $this->filePath = $filePath;
  }
  
  public function readCreate()
  {
    $create = new Sabel_Db_Migration_Create();
    include ($this->filePath);
    
    return $create->build();
  }
  
  public function readAddColumn()
  {
    $add = new Sabel_Db_Migration_AddColumn();
    include ($this->filePath);
    
    return $add->build();
  }
  
  public function readDropColumn()
  {
    $drop = new Sabel_Db_Migration_DropColumn();
    include ($this->filePath);
    
    return $drop;
  }
  
  public function readChangeColumn()
  {
    $change = new Sabel_Db_Migration_ChangeColumn();
    include ($this->filePath);
    
    return $change;
  }
  
  public function readQuery()
  {
    $query = new Sabel_Db_Migration_Query();
    include ($this->filePath);
    
    return $query;
  }
}
