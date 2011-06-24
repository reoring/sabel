<?php

$create->column("sid")->type(_STRING)
                      ->length(32)    # md5
                      //->length(40)  # sha1
                      ->primary(true);

$create->column("data")->type(_TEXT);

$create->column("timeout")->type(_INT)
                          ->value(0);
