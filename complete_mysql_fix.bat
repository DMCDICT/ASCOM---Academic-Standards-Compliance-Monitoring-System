@echo off
echo ========================================
echo Complete MySQL Fix for XAMPP
echo ========================================
echo.

echo Step 1: Stopping all MySQL processes...
taskkill /F /IM mysqld.exe 2>nul
timeout /t 3 /nobreak >nul

echo Step 2: Cleaning up old paths...
if exist "C:\xampp" (
    echo Found old C:\xampp directory - removing...
    rmdir /s /q "C:\xampp" 2>nul
)

echo Step 3: Checking MySQL configuration...
echo Current datadir setting:
findstr "datadir" "D:\xampp\mysql\bin\my.ini"

echo.
echo Step 4: Creating backup of my.ini...
copy "D:\xampp\mysql\bin\my.ini" "D:\xampp\mysql\bin\my.ini.backup"

echo Step 5: Starting MySQL with explicit parameters...
cd /d "D:\xampp\mysql\bin"
start /B mysqld.exe --defaults-file="D:\xampp\mysql\bin\my.ini" --datadir="D:\xampp\mysql\data" --console

echo Step 6: Waiting for MySQL to start...
timeout /t 10 /nobreak >nul

echo Step 7: Testing MySQL connection...
mysql.exe -u root -e "SELECT VERSION();" 2>nul
if %errorlevel% equ 0 (
    echo ✅ MySQL is working!
    echo.
    echo Step 8: Starting Apache...
    cd /d "D:\xampp"
    start /B apache\bin\httpd.exe
    timeout /t 5 /nobreak >nul
    
    echo Step 9: Testing PhpMyAdmin...
    echo.
    echo Please try accessing:
    echo - http://localhost/phpmyadmin
    echo - http://127.0.0.1/phpmyadmin
    echo.
    echo If PhpMyAdmin still loads slowly, try:
    echo - Incognito/Private mode
    echo - Clear browser cache
    echo - Different browser
) else (
    echo ❌ MySQL connection failed
    echo.
    echo Alternative solutions:
    echo 1. Check if port 3306 is in use
    echo 2. Try different MySQL port
    echo 3. Reset MySQL data directory
    echo.
    echo See MySQL_Path_Fix_Solution.md for detailed steps
)

echo.
echo Press any key to continue...
pause >nul 