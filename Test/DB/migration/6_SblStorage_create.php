<?php

$create->column("id")->type(_STRING)
                     ->length(64)  # client_id(32) + key(32)
                     ->primary(true);

$create->column("data")->type(_TEXT);

$create->column("timeout")->type(_INT)
                          ->value(0);
