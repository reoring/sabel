<?php

$create->column("id")->type(_INT)
                     ->primary(true);

$create->column("huga_id")->type(_INT)
                          ->nullable(false);

$create->column("foo_id")->type(_INT)
                         ->nullable(false);

$create->fkey("huga_id");
$create->fkey("foo_id")->onDelete("CASCADE");
$create->options("engine", "InnoDB");

