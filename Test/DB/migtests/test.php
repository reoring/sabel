<?php

define("RUN_BASE", getcwd());
require_once ("/usr/local/lib/php/Sabel/Sabel.php");
require_once (RUN_BASE . "/config/INIT.php");
require_once (RUN_BASE . "/config/environment.php");

class Counter
{
  public static $count = 1;
}

function equals($arg1, $arg2)
{
  if ($arg1 === $arg2) {
    echo ".";
  } else {
    echo "F ( {$arg1}, {$arg2} )";
  }

  if (Counter::$count !== 0 && (Counter::$count % 40) === 0) echo "\n";
  Counter::$count++;
}

function isTrue($arg1)
{
  if ($arg1 === true) {
    echo ".";
  } else {
    echo "F ( {$arg1} )";
  }

  if (Counter::$count !== 0 && (Counter::$count % 40) === 0) echo "\n";
  Counter::$count++;
}

function isFalse($arg1)
{
  if ($arg1 === false) {
    echo ".";
  } else {
    echo "F ( {$arg1} )";
  }

  if (Counter::$count !== 0 && (Counter::$count % 40) === 0) echo "\n";
  Counter::$count++;
}

function isNull($arg1)
{
  if ($arg1 === null) {
    echo ".";
  } else {
    echo "F ( {$arg1} )";
  }

  if (Counter::$count !== 0 && (Counter::$count % 40) === 0) echo "\n";
  Counter::$count++;
}

define("CONNAME", $_SERVER["argv"][1]);

$configs = array("sqlite" => array(
                   "package"  => "sabel.db.pdo.sqlite",
                   "database" => "/home/ebine/test.sq3"),
                 "mysql" => array(
                   "package"  => "sabel.db.mysql",
                   "host"     => "127.0.0.1",
                   "database" => "sdb_test",
                   "port"     => "3306",
                   "user"     => "root",
                   "password" => ""),
                 "pgsql" => array(
                   "package"  => "sabel.db.pgsql",
                   "host"     => "127.0.0.1",
                   "database" => "sdb_test",
                   "user"     => "pgsql",
                   "password" => "pgsql"),
                 "oci" => array(
                   "package"  => "sabel.db.oci",
                   "host"     => "127.0.0.1",
                   "database" => "XE",
                   "schema"   => "DEVELOP",
                   "user"     => "DEVELOP",
                   "password" => "DEVELOP")
                 );

foreach ($configs as $key => $param) {
  Sabel_Db_Config::add($key, $param);
}

echo "[ " . CONNAME . " ]\n";

$path = RUN_BASE . "/migration/tmp/1_Hoge_create.php";
system("php exec.php $path " . CONNAME . " upgrade");

$accessor = Sabel_Db::createMetadata(CONNAME);
$hoge = $accessor->getTable("hoge");
$id   = $hoge->id;
$name = $hoge->name;
$test = $hoge->test;
$body = $hoge->body;
$bool = $hoge->bool;

isTrue($id->isInt(true));
isTrue($id->primary);
isTrue($id->increment);
isNull($id->default);
isFalse($id->nullable);
equals($id->max, PHP_INT_MAX);

isTrue($name->isString());
isFalse($name->primary);
isFalse($name->increment);
equals($name->default, "default name");
isTrue($name->nullable);
equals($name->max, 128);

isTrue($test->isString());
isFalse($test->primary);
isFalse($test->increment);
isNull($test->default);
isTrue($test->nullable);
equals($test->max, 255);

isTrue($body->isText());
isFalse($body->primary);
isFalse($body->increment);
isNull($body->default);
isFalse($body->nullable);

isTrue($bool->isBool());
isFalse($bool->primary);
isFalse($bool->increment);
isFalse($bool->default);
isTrue($bool->nullable);

Sabel_Db_Connection::closeAll();

$path = RUN_BASE . "/migration/tmp/2_Hoge_addColumn.php";
system("php exec.php $path " . CONNAME . " upgrade");

$accessor = Sabel_Db::createMetadata(CONNAME);
$hoge = $accessor->getTable("hoge");
$id   = $hoge->id;
$name = $hoge->name;
$test = $hoge->test;
$body = $hoge->body;
$ft   = $hoge->ft;
$dbl  = $hoge->dbl;
$sint = $hoge->sint;
$bint = $hoge->bint;
$bool = $hoge->bool;

isTrue($id->isInt(true));
isTrue($id->primary);
isTrue($id->increment);
isNull($id->default);
isFalse($id->nullable);
equals($id->max, PHP_INT_MAX);

isTrue($name->isString());
isFalse($name->primary);
isFalse($name->increment);
equals($name->default, "default name");
isTrue($name->nullable);
equals($name->max, 128);

isTrue($test->isString());
isFalse($test->primary);
isFalse($test->increment);
isNull($test->default);
isTrue($test->nullable);
equals($test->max, 255);

isTrue($body->isText());
isFalse($body->primary);
isFalse($body->increment);
isNull($body->default);
isFalse($body->nullable);

isTrue($ft->isFloat());
isFalse($ft->primary);
isFalse($ft->increment);
equals($ft->default, 1.333);
isTrue($ft->nullable);

isTrue($dbl->isDouble());
isFalse($dbl->primary);
isFalse($dbl->increment);
equals($dbl->default, 1.23456);
isFalse($dbl->nullable);

isTrue($sint->isSmallint());
isFalse($sint->primary);
isFalse($sint->increment);
equals($sint->default, 30000);
isTrue($sint->nullable);

isTrue($bint->isBigint());
isFalse($bint->primary);
isFalse($bint->increment);
equals($bint->default, "400000000");
isTrue($bint->nullable);

isTrue($bool->isBool());
isFalse($bool->primary);
isFalse($bool->increment);
isFalse($bool->default);
isTrue($bool->nullable);

Sabel_Db_Connection::closeAll();

$path = RUN_BASE . "/migration/tmp/3_Hoge_dropColumn.php";
system("php exec.php $path " . CONNAME . " upgrade");

$accessor = Sabel_Db::createMetadata(CONNAME);
$hoge = $accessor->getTable("hoge");
$id   = $hoge->id;
$name = $hoge->name;
$test = $hoge->test;
$body = $hoge->body;
$bint = $hoge->bint;
$bool = $hoge->bool;

isTrue($id->isInt(true));
isTrue($id->primary);
isTrue($id->increment);
isNull($id->default);
isFalse($id->nullable);
equals($id->max, PHP_INT_MAX);

isTrue($name->isString());
isFalse($name->primary);
isFalse($name->increment);
equals($name->default, "default name");
isTrue($name->nullable);
equals($name->max, 128);

isTrue($test->isString());
isFalse($test->primary);
isFalse($test->increment);
isNull($test->default);
isTrue($test->nullable);
equals($test->max, 255);

isTrue($body->isText());
isFalse($body->primary);
isFalse($body->increment);
isNull($body->default);
isFalse($body->nullable);

isTrue($ft->isFloat());
isFalse($ft->primary);
isFalse($ft->increment);
equals($ft->default, 1.333);
isTrue($ft->nullable);

isTrue($bint->isBigint());
isFalse($bint->primary);
isFalse($bint->increment);
equals($bint->default, "400000000");
isTrue($bint->nullable);

isTrue($bool->isBool());
isFalse($bool->primary);
isFalse($bool->increment);
isFalse($bool->default);
isTrue($bool->nullable);

isNull($hoge->dbl);
isNull($hoge->sint);

Sabel_Db_Connection::closeAll();

$path = RUN_BASE . "/migration/tmp/4_Hoge_changeColumn.php";
system("php exec.php $path " . CONNAME . " upgrade");

$accessor = Sabel_Db::createMetadata(CONNAME);
$hoge = $accessor->getTable("hoge");
$id   = $hoge->id;
$name = $hoge->name;
$test = $hoge->test;
$body = $hoge->body;
$ft   = $hoge->ft;
$bint = $hoge->bint;
$bool = $hoge->bool;

isTrue($id->isInt(true));
isTrue($id->primary);
isTrue($id->increment);
isNull($id->default);
isFalse($id->nullable);
equals($id->max, PHP_INT_MAX);

isTrue($name->isString());
isFalse($name->primary);
isFalse($name->increment);
isNull($name->default);
isTrue($name->nullable);
equals($name->max, 128);

isTrue($test->isString());
isFalse($test->primary);
isFalse($test->increment);
isNull($test->default);
isFalse($test->nullable);
equals($test->max, 255);

isTrue($body->isText());
isFalse($body->primary);
isFalse($body->increment);
isNull($body->default);
isFalse($body->nullable);

isTrue($ft->isDouble());
isFalse($ft->primary);
isFalse($ft->increment);
equals($ft->default, 1.33333);
isTrue($ft->nullable);

isTrue($bint->isBigint());
isFalse($bint->primary);
isFalse($bint->increment);
equals($bint->default, "400000000");
isTrue($bint->nullable);

isTrue($bool->isBool());
isFalse($bool->primary);
isFalse($bool->increment);
isFalse($bool->default);
isTrue($bool->nullable);

isNull($hoge->dbl);
isNull($hoge->sint);

Sabel_Db_Connection::closeAll();

$path = RUN_BASE . "/migration/tmp/5_Huga_create.php";
system("php exec.php $path " . CONNAME . " upgrade");

$accessor = Sabel_Db::createMetadata(CONNAME);
$huga  = $accessor->getTable("huga");
$id    = $huga->id;
$email = $huga->email;

isTrue($id->isInt(true));
isTrue($id->primary);
isFalse($id->increment);
isNull($id->default);
isFalse($id->nullable);
equals($id->max, PHP_INT_MAX);

isTrue($email->isString());
isFalse($email->primary);
isFalse($email->increment);
isNull($email->default);
isFalse($email->nullable);
equals($email->max, 255);
isTrue($huga->isUnique("email"));

Sabel_Db_Connection::closeAll();

$path = RUN_BASE . "/migration/tmp/6_Foo_create.php";
system("php exec.php $path " . CONNAME . " upgrade");

$accessor = Sabel_Db::createMetadata(CONNAME);
$foo = $accessor->getTable("foo");
$id  = $foo->id;

isTrue($id->isInt(true));
isTrue($id->primary);
isFalse($id->increment);
isNull($id->default);
isFalse($id->nullable);
equals($id->max, PHP_INT_MAX);

Sabel_Db_Connection::closeAll();

$path = RUN_BASE . "/migration/tmp/7_Hoge_drop.php";
system("php exec.php $path " . CONNAME . " upgrade");

$accessor = Sabel_Db::createMetadata(CONNAME);
$tables = $accessor->getTableList();
isFalse(in_array("hoge", $tables));

Sabel_Db_Connection::closeAll();

$path = RUN_BASE . "/migration/tmp/8_Bar_create.php";
system("php exec.php $path " . CONNAME . " upgrade");

$accessor = Sabel_Db::createMetadata(CONNAME);
$bar = $accessor->getTable("bar");
$id  = $bar->id;
$huga_id = $bar->huga_id;
$foo_id  = $bar->foo_id;

isTrue($id->isInt(true));
isTrue($id->primary);
isFalse($id->increment);
isNull($id->default);
isFalse($id->nullable);
equals($id->max, PHP_INT_MAX);

isTrue($huga_id->isInt(true));
isFalse($huga_id->primary);
isFalse($huga_id->increment);
isNull($huga_id->default);
isFalse($huga_id->nullable);
equals($huga_id->max, PHP_INT_MAX);

isTrue($foo_id->isInt(true));
isFalse($foo_id->primary);
isFalse($foo_id->increment);
isNull($foo_id->default);
isFalse($foo_id->nullable);
equals($foo_id->max, PHP_INT_MAX);

if (CONNAME !== "sqlite") {
  $fkey = $bar->getForeignKey();
  isTrue($fkey->has("huga_id"));
  isTrue($fkey->has("foo_id"));
  $hugaId = $fkey->huga_id;
  $fooId  = $fkey->foo_id;
  equals($hugaId->table, "huga");
  equals($fooId->table,  "foo");
  equals($hugaId->column, "id");
  equals($fooId->column,  "id");
  equals($hugaId->onDelete, "NO ACTION");
  equals($fooId->onDelete,  "CASCADE");
  equals($hugaId->onUpdate, "NO ACTION");
  equals($fooId->onUpdate,  "NO ACTION");
}

Sabel_Db_Connection::closeAll();

$path = RUN_BASE . "/migration/tmp/8_Bar_create.php";
system("php exec.php $path " . CONNAME . " downgrade");

$accessor = Sabel_Db::createMetadata(CONNAME);
$tables = $accessor->getTableList();
isFalse(in_array("bar", $tables));

Sabel_Db_Connection::closeAll();

$path = RUN_BASE . "/migration/tmp/7_Hoge_drop.php";
system("php exec.php $path " . CONNAME . " downgrade");

$accessor = Sabel_Db::createMetadata(CONNAME);
$hoge = $accessor->getTable("hoge");
$id   = $hoge->id;
$name = $hoge->name;
$test = $hoge->test;
$body = $hoge->body;
$ft   = $hoge->ft;
$bint = $hoge->bint;
$bool = $hoge->bool;

isTrue($id->isInt(true));
isTrue($id->primary);
isTrue($id->increment);
isNull($id->default);
isFalse($id->nullable);
equals($id->max, PHP_INT_MAX);

isTrue($name->isString());
isFalse($name->primary);
isFalse($name->increment);
isNull($name->default);
isTrue($name->nullable);
equals($name->max, 128);

isTrue($test->isString());
isFalse($test->primary);
isFalse($test->increment);
isNull($test->default);
isFalse($test->nullable);
equals($test->max, 255);

isTrue($body->isText());
isFalse($body->primary);
isFalse($body->increment);
isNull($body->default);
isFalse($body->nullable);

isTrue($ft->isDouble());
isFalse($ft->primary);
isFalse($ft->increment);
equals($ft->default, 1.33333);
isTrue($ft->nullable);

isTrue($bint->isBigint());
isFalse($bint->primary);
isFalse($bint->increment);
equals($bint->default, "400000000");
isTrue($bint->nullable);

isTrue($bool->isBool());
isFalse($bool->primary);
isFalse($bool->increment);
isFalse($bool->default);
isTrue($bool->nullable);

isNull($hoge->dbl);
isNull($hoge->sint);

Sabel_Db_Connection::closeAll();

$path = RUN_BASE . "/migration/tmp/6_Foo_create.php";
system("php exec.php $path " . CONNAME . " downgrade");

$accessor = Sabel_Db::createMetadata(CONNAME);
$tables = $accessor->getTableList();
isFalse(in_array("foo", $tables));

Sabel_Db_Connection::closeAll();

$path = RUN_BASE . "/migration/tmp/5_Huga_create.php";
system("php exec.php $path " . CONNAME . " downgrade");

$accessor = Sabel_Db::createMetadata(CONNAME);
$tables = $accessor->getTableList();
isFalse(in_array("huga", $tables));

Sabel_Db_Connection::closeAll();

$path = RUN_BASE . "/migration/tmp/4_Hoge_changeColumn.php";
system("php exec.php $path " . CONNAME . " downgrade");

$accessor = Sabel_Db::createMetadata(CONNAME);
$hoge = $accessor->getTable("hoge");
$id   = $hoge->id;
$name = $hoge->name;
$test = $hoge->test;
$body = $hoge->body;
$ft   = $hoge->ft;
$bint = $hoge->bint;
$bool = $hoge->bool;

isTrue($id->isInt(true));
isTrue($id->primary);
isTrue($id->increment);
isNull($id->default);
isFalse($id->nullable);
equals($id->max, PHP_INT_MAX);

isTrue($name->isString());
isFalse($name->primary);
isFalse($name->increment);
equals($name->default, "default name");
isTrue($name->nullable);
equals($name->max, 128);

isTrue($test->isString());
isFalse($test->primary);
isFalse($test->increment);
isNull($test->default);
isTrue($test->nullable);
equals($test->max, 255);

isTrue($body->isText());
isFalse($body->primary);
isFalse($body->increment);
isNull($body->default);
isFalse($body->nullable);

isTrue($ft->isFloat());
isFalse($ft->primary);
isFalse($ft->increment);
equals($ft->default, 1.333);
isTrue($ft->nullable);

isTrue($bint->isBigint());
isFalse($bint->primary);
isFalse($bint->increment);
equals($bint->default, "400000000");
isTrue($bint->nullable);

isTrue($bool->isBool());
isFalse($bool->primary);
isFalse($bool->increment);
isFalse($bool->default);
isTrue($bool->nullable);

isNull($hoge->dbl);
isNull($hoge->sint);

$tables = $accessor->getTableList();
isFalse(in_array("huga", $tables));
isFalse(in_array("foo",  $tables));

Sabel_Db_Connection::closeAll();

$path = RUN_BASE . "/migration/tmp/3_Hoge_dropColumn.php";
system("php exec.php $path " . CONNAME . " downgrade");

$accessor = Sabel_Db::createMetadata(CONNAME);
$hoge = $accessor->getTable("hoge");
$id   = $hoge->id;
$name = $hoge->name;
$test = $hoge->test;
$body = $hoge->body;
$ft   = $hoge->ft;
$dbl  = $hoge->dbl;
$sint = $hoge->sint;
$bint = $hoge->bint;
$bool = $hoge->bool;

isTrue($id->isInt(true));
isTrue($id->primary);
isTrue($id->increment);
isNull($id->default);
isFalse($id->nullable);
equals($id->max, PHP_INT_MAX);

isTrue($name->isString());
isFalse($name->primary);
isFalse($name->increment);
equals($name->default, "default name");
isTrue($name->nullable);
equals($name->max, 128);

isTrue($test->isString());
isFalse($test->primary);
isFalse($test->increment);
isNull($test->default);
isTrue($test->nullable);
equals($test->max, 255);

isTrue($body->isText());
isFalse($body->primary);
isFalse($body->increment);
isNull($body->default);
isFalse($body->nullable);

isTrue($ft->isFloat());
isFalse($ft->primary);
isFalse($ft->increment);
equals($ft->default, 1.333);
isTrue($ft->nullable);

isTrue($dbl->isDouble());
isFalse($dbl->primary);
isFalse($dbl->increment);
equals($dbl->default, 1.23456);
isFalse($dbl->nullable);

isTrue($sint->isSmallint());
isFalse($sint->primary);
isFalse($sint->increment);
equals($sint->default, 30000);
isTrue($sint->nullable);

isTrue($bint->isBigint());
isFalse($bint->primary);
isFalse($bint->increment);
equals($bint->default, "400000000");
isTrue($bint->nullable);

isTrue($bool->isBool());
isFalse($bool->primary);
isFalse($bool->increment);
isFalse($bool->default);
isTrue($bool->nullable);

Sabel_Db_Connection::closeAll();

$path = RUN_BASE . "/migration/tmp/2_Hoge_addColumn.php";
system("php exec.php $path " . CONNAME . " downgrade");

$accessor = Sabel_Db::createMetadata(CONNAME);
$hoge = $accessor->getTable("hoge");
$id   = $hoge->id;
$name = $hoge->name;
$test = $hoge->test;
$body = $hoge->body;
$bool = $hoge->bool;

isTrue($id->isInt(true));
isTrue($id->primary);
isTrue($id->increment);
isNull($id->default);
isFalse($id->nullable);
equals($id->max, PHP_INT_MAX);

isTrue($name->isString());
isFalse($name->primary);
isFalse($name->increment);
equals($name->default, "default name");
isTrue($name->nullable);
equals($name->max, 128);

isTrue($test->isString());
isFalse($test->primary);
isFalse($test->increment);
isNull($test->default);
isTrue($test->nullable);
equals($test->max, 255);

isTrue($body->isText());
isFalse($body->primary);
isFalse($body->increment);
isNull($body->default);
isFalse($body->nullable);

isTrue($bool->isBool());
isFalse($bool->primary);
isFalse($bool->increment);
isFalse($bool->default);
isTrue($bool->nullable);

isNull($hoge->ft);
isNull($hoge->dbl);
isNull($hoge->sint);
isNull($hoge->bint);

Sabel_Db_Connection::closeAll();

$path = RUN_BASE . "/migration/tmp/1_Hoge_create.php";
system("php exec.php $path " . CONNAME . " downgrade");

$accessor = Sabel_Db::createMetadata(CONNAME);
$tables = $accessor->getTableList();
isFalse(in_array("hoge", $tables));

Sabel_Db_Connection::closeAll();

echo "\n";
