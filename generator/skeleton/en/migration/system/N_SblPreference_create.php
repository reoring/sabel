<?php

$create->column("namespace")
       ->type(_STRING)
       ->length(64);

$create->column("key")
       ->type(_STRING)
       ->length(64);

$create->column("value")
       ->type(_STRING)
       ->length(1024);
       
$create->column("type")
       ->type(_STRING)
       ->length(32);

$create->primary(array("namespace", "key"));

$create->options("engine", "InnoDB");