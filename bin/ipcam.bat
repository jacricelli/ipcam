@echo off

SET app=%0
SET lib=%~dp0

php "%lib%ipcam.php" %*

echo.

exit /B %ERRORLEVEL%
