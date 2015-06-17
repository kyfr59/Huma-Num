@echo off

ECHO :: Welcome to Silo console client ::
ECHO.

SET inputFolder=input
set /p inputFolder=Enter packets folder location [press enter for "input"]:

SET outputFolder=output
set /p outputFolder=Enter output folder location [press enter for "output"]:

SET errorFolder=error
set /p errorFolder=Enter error folder location [press enter for "error"]:

:FACILE
SET /P FACILE=Include facile validation on server (Y/N)?
if "%FACILE%"=="" GOTO FACILE
if "%FACILE%"=="y" SET FACILE=-facileValidation
if "%FACILE%"=="Y" SET FACILE=-facileValidation
if "%FACILE%"=="n" SET FACILE=
if "%FACILE%"=="N" SET FACILE=

:EMAIL
set /p email=Enter email address:
if "%email%"=="" GOTO EMAIL

java -jar nakala-console.jar -email %email% -inputFolder %inputFolder% -outputFolder %outputFolder% -errorFolder %errorFolder% %FACILE%
pause