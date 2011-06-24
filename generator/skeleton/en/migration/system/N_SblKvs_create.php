<?php

$create->column("key")->type(_STRING)->length(64)->primary(true);
$create->column("value")->type(_TEXT);
$create->column("timeout")->type(_INT)->value(0);
