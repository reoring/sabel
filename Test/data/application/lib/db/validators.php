<?php

/*
$emailValidator = array("function" => "validateEmailAddress",
                        "model"    => "MODEL_NAME",
                        "column"   => "COLUMN_NAME");

$passwdValidator = array("function"  => "validatePasswords",
                         "model"     => "MODEL_NAME",
                         "column"    => "COLUMN_NAME",
                         "arguments" => "REINPUT");
*/

// Sabel_Db_Validate_Config::addValidator($emailValidator);
// Sabel_Db_Validate_Config::addValidator($passwdValidator);

function checkEmailAddress($email)
{
  $regex = '/^[\w.\-_]+@([\w\-_]+\.)+[a-zA-Z]+$/';
  return (preg_match($regex, $email) !== 0);
}

function validateEmailAddress($model, $name, $localizedName)
{
  if ($model->$name !== null && !checkEmailAddress($model->$name)) {
    return "invalid mail address format.";
  }
}

function validatePasswords($model, $name, $localizedName, $reInput)
{
  if ($model->$name !== $model->$reInput) {
    return "passwords didn't match.";
  } else {
    $model->unsetValue($reInput);
  }
}
