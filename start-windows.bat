@echo off
REM Start Fluxwave POS application using Docker Compose
REM This script is for Windows clients

cd /d "%~dp0"

echo Starting Fluxwave POS...
echo.

docker-compose up -d --build

if %ERRORLEVEL% NEQ 0 (
    echo.
    echo ERROR: Failed to start the application.
    echo Please ensure Docker Desktop is running.
    echo Check https://docs.docker.com/docker-for-windows/ for setup instructions.
    pause
    exit /b 1
)

echo.
echo Fluxwave POS is starting...
echo.
echo Access the application at: http://localhost:80
echo.
echo Default credentials:
echo   Username: admin
echo   Password: pointofsale
echo.
echo To stop the application, run: stop-windows.bat
echo.

pause
