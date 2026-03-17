@echo off
echo ========================================
echo PhpMyAdmin Loading Issue Fix
echo ========================================
echo.

echo Step 1: Stopping all XAMPP services...
taskkill /F /IM mysqld.exe 2>nul
taskkill /F /IM httpd.exe 2>nul
taskkill /F /IM xampp-control.exe 2>nul
timeout /t 3 /nobreak >nul

echo Step 2: Cleaning temporary files...
if exist "D:\xampp\phpMyAdmin\tmp" rmdir /s /q "D:\xampp\phpMyAdmin\tmp"
if exist "D:\xampp\apache\logs" rmdir /s /q "D:\xampp\apache\logs"
mkdir "D:\xampp\phpMyAdmin\tmp" 2>nul
mkdir "D:\xampp\apache\logs" 2>nul

echo Step 3: Starting XAMPP Control Panel...
start "" "D:\xampp\xampp-control.exe"

echo Step 4: Instructions for manual steps...
echo.
echo Please follow these steps:
echo 1. In XAMPP Control Panel, click "Start" for Apache
echo 2. Wait 10 seconds, then click "Start" for MySQL
echo 3. Wait for both services to show green status
echo 4. Open browser and go to: http://localhost/phpmyadmin
echo.
echo If PhpMyAdmin still loads slowly:
echo - Try: http://127.0.0.1/phpmyadmin
echo - Try incognito/private mode
echo - Clear browser cache and cookies
echo.
pause 