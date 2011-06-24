@echo off

if "%OS%"=="Windows_NT" @setlocal

set SABEL=c:\php\includes\Sabel

goto init

:init

if "%PHP_COMMAND%" == "" goto no_phpcommand

%PHP_COMMAND% -d html_errors=off -d open_basedir= -q "%SABEL%\sabel\Sakle.php" %1 %2 %3 %4 %5 %6 %7 %8 %9

goto cleanup

:no_phpcommand
set PHP_COMMAND=php
goto init

:cleanup
if "%OS%"=="Windows_NT" @endlocal
rem pause
