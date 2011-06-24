<?php

function MODEL($mdlName, $id = null)
{
  static $cache = array();
  
  if (isset($cache[$mdlName])) {
    if ($cache[$mdlName]) {
      return new $mdlName($id);
    } else {
      return new Sabel_Db_Model_Proxy($mdlName, $id);
    }
  }
  
  if (!$exists = class_exists($mdlName, false)) {
    $path = MODELS_DIR_PATH . DS . $mdlName . ".php";
    $exists = Sabel::fileUsing($path, true);
  }
  
  $cache[$mdlName] = $exists;
  
  if ($exists) {
    return new $mdlName($id);
  } else {
    return new Sabel_Db_Model_Proxy($mdlName, $id);
  }
}

function is_model($model)
{
  return ($model instanceof Sabel_Db_Model);
}

function db_query($sql, array $params = array(), $connectionName = "default")
{
  return Sabel_Db::createStatement($connectionName)->setQuery($sql)->binds($params)->execute();
}

function finder($mdlName, $projection = null)
{
  return new Sabel_Db_Finder($mdlName, $projection);
}

function eq($column, $value)
{
  return Sabel_Db_Condition::create(
    Sabel_Db_Condition::EQUAL, $column, $value
  );
}

function neq($column, $value)
{
  return Sabel_Db_Condition::create(
    Sabel_Db_Condition::EQUAL, $column, $value, true
  );
}

function in($column, array $values)
{
  return Sabel_Db_Condition::create(
    Sabel_Db_Condition::IN, $column, $values
  );
}

function nin($column, array $values)
{
  return Sabel_Db_Condition::create(
    Sabel_Db_Condition::IN, $column, $values, true
  );
}

function lt($column, $value)
{
  return Sabel_Db_Condition::create(
    Sabel_Db_Condition::LESS_THAN, $column, $value
  );
}

function le($column, $value)
{
  return Sabel_Db_Condition::create(
    Sabel_Db_Condition::LESS_EQUAL, $column, $value
  );
}

function gt($column, $value)
{
  return Sabel_Db_Condition::create(
    Sabel_Db_Condition::GREATER_THAN, $column, $value
  );
}

function ge($column, $value)
{
  return Sabel_Db_Condition::create(
    Sabel_Db_Condition::GREATER_EQUAL, $column, $value
  );
}

function bw($column, $from, $to = null)
{
  if ($to === null) {
    return __bw($column, $from, false);
  } else {
    return __bw($column, array($from, $to), false);
  }
}

function nbw($column, $from, $to = null)
{
  if ($to === null) {
    return __bw($column, $from, true);
  } else {
    return __bw($column, array($from, $to), true);
  }
}

function __bw($column, array $params, $not)
{
  if (isset($params["from"])) $params[0] = $params["from"];
  if (isset($params["to"]))   $params[1] = $params["to"];
  
  unset($params["from"], $params["to"]);
  
  return Sabel_Db_Condition::create(
    Sabel_Db_Condition::BETWEEN, $column, $params, $not
  );
}

function starts($column, $value)
{
  return Sabel_Db_Condition::create(
    Sabel_Db_Condition::LIKE, $column, $value
  )->type(Sabel_Db_Condition_Like::STARTS_WITH);
}

function ends($column, $value)
{
  return Sabel_Db_Condition::create(
    Sabel_Db_Condition::LIKE, $column, $value
  )->type(Sabel_Db_Condition_Like::ENDS_WITH);
}

function contains($column, $value)
{
  return Sabel_Db_Condition::create(
    Sabel_Db_Condition::LIKE, $column, $value
  )->type(Sabel_Db_Condition_Like::CONTAINS);
}

function isNull($column)
{
  return Sabel_Db_Condition::create(
    Sabel_Db_Condition::ISNULL, $column
  );
}

function isNotNull($column)
{
  return Sabel_Db_Condition::create(
    Sabel_Db_Condition::ISNOTNULL, $column
  );
}

function ow(/* args */)
{
  $or = new Sabel_Db_Condition_Or();
  foreach (func_get_args() as $condition) {
    $or->add($condition);
  }
  
  return $or;
}

function aw(/* args */)
{
  $and = new Sabel_Db_Condition_And();
  foreach (func_get_args() as $condition) {
    $and->add($condition);
  }
  
  return $and;
}

function rel($mdlName)
{
  return new Sabel_Db_Join_Relation($mdlName);
}

function create_join_key(Sabel_Db_Model $childModel, $parentName)
{
  if ($fkey = $childModel->getMetadata()->getForeignKey()) {
    foreach ($fkey->toArray() as $colName => $fkey) {
      if ($fkey->table === $parentName) {
        return array("id" => $fkey->column, "fkey" => $colName);
      }
    }
  }
  
  return array("id" => "id", "fkey" => $parentName . "_id");
}

function convert_to_tablename($mdlName)
{
  static $cache = array();
  
  if (isset($cache[$mdlName])) {
    return $cache[$mdlName];
  }
  
  if (preg_match("/^[a-z0-9_]+$/", $mdlName)) {
    $tblName = $mdlName;
  } else {
    $tblName = substr(strtolower(preg_replace("/([A-Z])/", '_$1', $mdlName)), 1);
  }
  
  return $cache[$mdlName] = $tblName;
}

function convert_to_modelname($tblName)
{
  static $cache = array();
  
  if (isset($cache[$tblName])) {
    return $cache[$tblName];
  } else {
    $mdlName = implode("", array_map("ucfirst", explode("_", $tblName)));
    return $cache[$tblName] = $mdlName;
  }
}
