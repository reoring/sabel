<?php

$create->column("name")->type(_STRING)
                       ->nullable(false);

$create->column("namespace")->type(_STRING)
                            ->nullable(false);

$create->column("contents")->type(_TEXT);

$create->primary(array("name", "namespace"));
