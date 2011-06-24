<?php

$create->column("id")->type(_INT)->primary(true);
$create->column("grandparents_id")->type(_INT)->nullable(false);
$create->column("value")->type(_STRING)->nullable(false);
$create->fkey("grandparents_id");
$create->options("engine", "InnoDB");
