<?php

$create->column("sid")->type(_STRING)
                      ->length(64)
                      ->primary(true);

$create->column("sdata")->type(_TEXT);

$create->column("timeout")->type(_INT)
                          ->value(0);
