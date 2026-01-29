@echo off
setlocal

:: Get the directory where this script is located
cd /d "%~dp0"

echo ==========================================
echo Starting Antigravity POS Development Environment
echo ==========================================
echo.

:: 1. Start the Queue Worker (in a new window)
echo [1/2] Starting Queue Worker...
start "Antigravity Queue (Do not close)" php artisan queue:listen

:: 2. Start the Vite Development Server (in a new window)
echo [2/2] Starting Vite Asset Server...
start "Antigravity Vite (Do not close)" npm run dev

echo.
echo ==========================================
echo All services started! 
echo You can now access the site at your Laragon URL (e.g., http://antigravity-pos.test)
echo.
echo To stop them, simply close the opened windows.
echo ==========================================
pause
