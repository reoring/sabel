<?php

class Schema_Example
{
  public static function get()
  {
    $cols = array();

    $cols['column1'] = array('type'      => Sabel_Db_Type::INT,
                             'min'       => 0,
                             'max'       => PHP_INT_MAX,
                             'increment' => false,
                             'nullable'  => false,
                             'primary'   => true,
                             'default'   => null);

    $cols['column2'] = array('type'      => Sabel_Db_Type::STRING,
                             'max'       => 255,
                             'increment' => false,
                             'nullable'  => false,
                             'primary'   => false,
                             'default'   => null);

    return $cols;
  }

  public function getProperty()
  {
    $property = array();

    $property["tableEngine"] = null;
    $property["uniques"]     = null;
    $property["fkeys"]       = null;

    return $property;
  }
}
