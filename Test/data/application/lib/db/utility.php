<?php

class Condition extends Sabel_Db_Condition {}
class SqlPart   extends Sabel_Db_Sql_Part {}

define("EQUAL",         Condition::EQUAL);
define("ISNULL",        Condition::ISNULL);
define("ISNOTNULL",     Condition::ISNOTNULL);
define("IN",            Condition::IN);
define("BETWEEN",       Condition::BETWEEN);
define("LIKE",          Condition::LIKE);
define("GREATER_EQUAL", Condition::GREATER_EQUAL);
define("GREATER_THAN",  Condition::GREATER_THAN);
define("LESS_EQUAL",    Condition::LESS_EQUAL);
define("LESS_THAN",     Condition::LESS_THAN);
define("DIRECT",        Condition::DIRECT);

define("LIKE_BEGINS_WITH", Sabel_Db_Condition_Like::BEGINS_WITH);
define("LIKE_ENDS_WITH",   Sabel_Db_Condition_Like::ENDS_WITH);
define("LIKE_CONTAINS",    Sabel_Db_Condition_Like::CONTAINS);
define("LIKE_FIXED",       Sabel_Db_Condition_Like::FIXED);
