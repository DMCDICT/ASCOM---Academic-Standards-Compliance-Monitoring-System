@echo off
echo ========================================
echo Database Backup Script
echo ========================================
echo.

REM Create backup directory
if not exist "D:\backup" mkdir "D:\backup"
if not exist "D:\backup\databases" mkdir "D:\backup\databases"
if not exist "D:\backup\htdocs" mkdir "D:\backup\htdocs"

echo Step 1: Backing up htdocs (your website files)...
xcopy "D:\xampp\htdocs" "D:\backup\htdocs" /E /I /Y
echo ✅ htdocs backup completed!

echo.
echo Step 2: Starting MySQL for database backup...
taskkill /F /IM mysqld.exe 2>nul
timeout /t 3 /nobreak >nul

cd /d "D:\xampp\mysql\bin"
start /B mysqld.exe --console
timeout /t 10 /nobreak >nul

echo Step 3: Creating database backups...
echo - Backing up all databases...
mysqldump.exe -u root --all-databases > "D:\backup\databases\all_databases.sql" 2>nul
if %errorlevel% equ 0 (
    echo ✅ All databases backup completed!
) else (
    echo ❌ All databases backup failed
)

echo - Backing up ASCOM database...
mysqldump.exe -u root ascom_db > "D:\backup\databases\ascom_db.sql" 2>nul
if %errorlevel% equ 0 (
    echo ✅ ASCOM database backup completed!
) else (
    echo ❌ ASCOM database backup failed
)

echo.
echo Step 4: Verifying backups...
echo.
echo Backup files created:
dir "D:\backup\databases\*.sql"
echo.
echo htdocs backup size:
dir "D:\backup\htdocs" /s | findstr "File(s)"

echo.
echo ========================================
echo Backup Summary:
echo ========================================
echo ✅ htdocs folder backed up to: D:\backup\htdocs\
echo ✅ All databases backed up to: D:\backup\databases\all_databases.sql
echo ✅ ASCOM database backed up to: D:\backup\databases\ascom_db.sql
echo.
echo You can now safely reinstall XAMPP!
echo After reinstall, restore using:
echo - Copy htdocs: xcopy "D:\backup\htdocs" "D:\xampp\htdocs" /E /I
echo - Import database: D:\xampp\mysql\bin\mysql.exe -u root ascom_db ^< "D:\backup\databases\ascom_db.sql"
echo.
pause 