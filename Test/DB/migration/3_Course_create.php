<?php

$create->column("id")->type(_INT)
                     ->primary(true);

$create->column("name")->type(_STRING)
                       ->nullable(false);

$create->options("engine", "InnoDB");
