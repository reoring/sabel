<?php

$create->column("student_id")->type(_INT)
                             ->nullable(false);

$create->column("course_id")->type(_INT)
                            ->nullable(false);

$create->column("val")->type(_STRING)
                      ->nullable(false);

$create->primary(array("student_id", "course_id"));
$create->options("engine", "InnoDB");
