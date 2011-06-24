<?php

/**
 * Sabel_View_Location_Database
 *
 * @category   View
 * @package    org.sabel.view
 * @author     Ebine Yutaka <ebine.yutaka@sabel.jp>
 * @copyright  2004-2008 Mori Reo <mori.reo@sabel.jp>
 * @license    http://www.opensource.org/licenses/bsd-license.php  BSD License
 */
class Sabel_View_Location_Database extends Sabel_View_Location
{
  /**
   * @var string
   */
  protected $connectionName = "default";
  
  /**
   * @var string
   */
  protected $tableName = "sbl_template";
  
  /**
   * @var string
   */
  protected $namespace = "";
  
  /**
   * @var string
   */
  protected $contents  = "";
  
  /**
   * @param string $name
   *
   * @return void
   */
  public function setConnectionName($name)
  {
    $this->connectionName = $name;
  }
  
  /**
   * @param string $tblName
   *
   * @return void
   */
  public function setTableName($tblName)
  {
    $this->tableName = $tblName;
  }
  
  /**
   * @param string $namespace
   *
   * @return void
   */
  public function setNameSpace($namespace)
  {
    $this->namespace = $namespace;
  }
  
  /**
   * @param string $name
   *
   * @return string
   */
  public function name($name = null)
  {
    if ($name !== null) $this->contents = false;
    return parent::name($name);
  }
  
  /**
   * @return string
   */
  public function getContents()
  {
    if ($this->contents === false) {
      $contents = $this->_getContents();
      if ($contents === null) $contents = "";
      return $this->contents = $contents;
    } else {
      return $this->contents;
    }
  }
  
  public function create($contents = "")
  {
    $stmt = $this->createStatement();
    $stmt->type(Sabel_Db_Statement::INSERT)
         ->values(array("name"      => $this->_getPath(),
                        "namespace" => $this->namespace,
                        "contents"  => $contents));
    
    $stmt->execute();
    $this->contents = $contents;
  }
  
  public function delete()
  {
    $stmt = $this->createStatement();
    $stmt->type(Sabel_Db_Statement::DELETE)
         ->where("WHERE " . $stmt->quoteIdentifier("name") . " = @name@")
         ->bind("name", $this->_getPath())
         ->execute();
    
    $this->contents = "";
  }
  
  public function isValid()
  {
    if (($contents = $this->_getContents()) === null) {
      $this->contents = "";
      return false;
    } else {
      $this->contents = $contents;
      return true;
    }
  }
  
  private function _getContents()
  {
    $stmt  = $this->createStatement();
    $nCol  = $stmt->quoteIdentifier("name");
    $nsCol = $stmt->quoteIdentifier("namespace");
    
    $stmt->type(Sabel_Db_Statement::SELECT)
         ->projection(array("contents"))
         ->where("WHERE $nCol = @name@ AND $nsCol = @namespace@")
         ->bind("name", $this->_getPath())
         ->bind("namespace", $this->namespace);

    $result = $stmt->execute();
    return ($result === null) ? null : $result[0]["contents"];
  }
  
  public function _getPath()
  {
    return $this->viewDirPath . $this->name;
  }
  
  private function createStatement()
  {
    $stmt = Sabel_Db::createStatement($this->connectionName);
    $stmt->setMetadata(Sabel_Db_Metadata::getTableInfo($this->tableName, $this->connectionName));
    return $stmt;
  }
}
