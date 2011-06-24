<?php

$create->column("id")->type(_INT)->primary(true)->increment(true);
$create->column("name")->type(_STRING)->length(128)->nullable(false)->value("default name");
$create->column("email")->type(_STRING)->nullable(false);
$create->column("bint")->type(_BIGINT)->value("90000000000");
$create->column("sint")->type(_SMALLINT)->value(30000);
$create->column("txt")->type(_TEXT);
$create->column("bl")->type(_BOOL);
$create->column("ft")->type(_FLOAT)->value(10.234);
$create->column("dbl")->type(_DOUBLE)->value(10.23456);
$create->column("dt")->type(_DATE);
$create->column("idata")->type(_BINARY);
$create->unique("email");
