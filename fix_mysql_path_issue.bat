@echo off
echo ========================================
echo MySQL Path Issue Fix
echo ========================================
echo.

echo Step 1: Stopping all MySQL processes...
taskkill /F /IM mysqld.exe 2>nul
timeout /t 3 /nobreak >nul

echo Step 2: Checking MySQL configuration...
echo Current datadir setting:
findstr "datadir" "D:\xampp\mysql\bin\my.ini"

echo.
echo Step 3: Creating backup of my.ini...
copy "D:\xampp\mysql\bin\my.ini" "D:\xampp\mysql\bin\my.ini.backup"

echo Step 4: Starting MySQL with explicit datadir...
cd /d "D:\xampp\mysql\bin"
mysqld.exe --defaults-file="D:\xampp\mysql\bin\my.ini" --datadir="D:\xampp\mysql\data" --console

echo.
echo If MySQL starts successfully, you should see:
echo - "mysqld: ready for connections"
echo - No more "c:\xampp" errors
echo.
echo Press Ctrl+C to stop MySQL when done testing
pause 