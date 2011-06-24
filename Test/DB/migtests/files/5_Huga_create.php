<?php

$create->column("id")->type(_INT)
                     ->primary(true);

$create->column("email")->type(_STRING)
                        ->length(255)
                        ->nullable(false);

$create->unique("email");
$create->options("engine", "InnoDB");
