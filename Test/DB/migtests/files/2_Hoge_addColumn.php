<?php

$add->column("ft")->type(_FLOAT)
                   ->value(1.333);

$add->column("dbl")->type(_DOUBLE)
                  ->nullable(false)
                  ->value(1.23456);

$add->column("sint")->type(_SMALLINT)
                    ->value(30000);

$add->column("bint")->type(_BIGINT)
                    ->value(400000000);

