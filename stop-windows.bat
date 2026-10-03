@echo off
REM Stop Fluxwave POS application using Docker Compose
REM This script is for Windows clients

cd /d "%~dp0"

echo Stopping Fluxwave POS...
echo.

docker-compose down

if %ERRORLEVEL% NEQ 0 (
    echo.
    echo ERROR: Failed to stop the application.
    echo Please ensure Docker Desktop is running.
    pause
    exit /b 1
)

echo.
echo Fluxwave POS has been stopped.
echo.

pause
