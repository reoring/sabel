<?php

$create->column("id")->type(_INT)
                     ->primary(true)
                     ->increment(true);

$create->column("name")->type(_STRING)
                       ->length(128)
                       ->value("default name");

$create->column("test")->type(_STRING)
                       ->nullable(true);

$create->column("body")->type(_TEXT)
                       ->nullable(false);

$create->column("bool")->type(_BOOL)
                       ->value(false);

$create->options("engine", "InnoDB");
