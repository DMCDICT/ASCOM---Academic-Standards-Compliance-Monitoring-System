# Database Backup Guide - Before XAMPP Reinstall

## Method 1: Using mysqldump (Recommended)

### **Step 1: Backup All Databases**
```cmd
REM Navigate to MySQL bin directory
cd D:\xampp\mysql\bin

REM Backup all databases to a single file
mysqldump.exe -u root --all-databases > "D:\backup\all_databases.sql"
```

### **Step 2: Backup Specific Database (ascom_db)**
```cmd
REM Backup only your ASCOM database
mysqldump.exe -u root ascom_db > "D:\backup\ascom_db_backup.sql"
```

### **Step 3: Backup Database Structure Only (No Data)**
```cmd
REM Backup structure only (tables without data)
mysqldump.exe -u root --no-data ascom_db > "D:\backup\ascom_db_structure.sql"
```

### **Step 4: Backup Data Only (No Structure)**
```cmd
REM Backup data only (data without CREATE statements)
mysqldump.exe -u root --no-create-info ascom_db > "D:\backup\ascom_db_data.sql"
```

## Method 2: Using PhpMyAdmin (If It's Working)

### **Step 1: Export via PhpMyAdmin**
1. **Open PhpMyAdmin:** `http://localhost/phpmyadmin`
2. **Select your database** (ascom_db)
3. **Click "Export" tab**
4. **Choose "SQL" format**
5. **Select options:**
   - ✅ **Structure**
   - ✅ **Data**
   - ✅ **Add CREATE DATABASE**
   - ✅ **Add DROP DATABASE**
6. **Click "Go" to download**

### **Step 2: Save the File**
- Save as: `D:\backup\ascom_db_phpmyadmin.sql`

## Method 3: Direct File Copy (Alternative)

### **Step 1: Stop MySQL First**
```cmd
taskkill /F /IM mysqld.exe
```

### **Step 2: Copy Database Files**
```cmd
REM Backup your specific database folder
xcopy "D:\xampp\mysql\data\ascom_db" "D:\backup\mysql_data\ascom_db" /E /I

REM Backup MySQL system databases (optional)
xcopy "D:\xampp\mysql\data\mysql" "D:\backup\mysql_data\mysql" /E /I
```

### **Step 3: Restart MySQL**
```cmd
cd D:\xampp
xampp-control.exe
```

## Method 4: Automated Backup Script

### **Create a backup script:**
```batch
@echo off
echo ========================================
echo Database Backup Script
echo ========================================

REM Create backup directory
mkdir "D:\backup" 2>nul

REM Stop MySQL to ensure clean backup
echo Stopping MySQL...
taskkill /F /IM mysqld.exe 2>nul
timeout /t 3 /nobreak >nul

REM Start MySQL for backup
echo Starting MySQL...
cd /d "D:\xampp\mysql\bin"
start /B mysqld.exe --console
timeout /t 10 /nobreak >nul

REM Backup all databases
echo Creating backup...
mysqldump.exe -u root --all-databases > "D:\backup\all_databases_%date:~-4,4%%date:~-10,2%%date:~-7,2%_%time:~0,2%%time:~3,2%.sql"

REM Backup specific database
mysqldump.exe -u root ascom_db > "D:\backup\ascom_db_%date:~-4,4%%date:~-10,2%%date:~-7,2%_%time:~0,2%%time:~3,2%.sql"

echo Backup completed!
echo Files saved to D:\backup\
pause
```

## Verification Steps

### **Test Your Backup:**
```cmd
REM Check if backup file exists and has content
dir "D:\backup\*.sql"

REM View first few lines of backup
type "D:\backup\ascom_db_backup.sql" | more
```

### **Test Restore (Optional):**
```cmd
REM Test restore to a temporary database
D:\xampp\mysql\bin\mysql.exe -u root -e "CREATE DATABASE test_restore;"
D:\xampp\mysql\bin\mysql.exe -u root test_restore < "D:\backup\ascom_db_backup.sql"
```

## Complete Backup Process

### **Step 1: Create Backup Directory**
```cmd
mkdir "D:\backup"
mkdir "D:\backup\databases"
mkdir "D:\backup\htdocs"
```

### **Step 2: Backup Everything**
```cmd
REM Backup htdocs (your website files)
xcopy "D:\xampp\htdocs" "D:\backup\htdocs" /E /I

REM Backup all databases
cd D:\xampp\mysql\bin
mysqldump.exe -u root --all-databases > "D:\backup\databases\all_databases.sql"

REM Backup specific database
mysqldump.exe -u root ascom_db > "D:\backup\databases\ascom_db.sql"
```

### **Step 3: Verify Backups**
```cmd
REM Check file sizes
dir "D:\backup\databases\*.sql"

REM Check htdocs backup
dir "D:\backup\htdocs" /s
```

## After Clean Reinstall - Restore Process

### **Step 1: Create Database**
```sql
CREATE DATABASE ascom_db;
```

### **Step 2: Import Your Data**
```cmd
REM Import your database
D:\xampp\mysql\bin\mysql.exe -u root ascom_db < "D:\backup\databases\ascom_db.sql"
```

### **Step 3: Restore htdocs**
```cmd
REM Copy back your website files
xcopy "D:\backup\htdocs" "D:\xampp\htdocs" /E /I
```

## Important Notes

### **What to Backup:**
- ✅ **Your website files** (htdocs folder)
- ✅ **Your database structure and data**
- ✅ **Any custom configurations**

### **What NOT to Backup:**
- ❌ **MySQL system databases** (mysql, performance_schema, etc.)
- ❌ **MySQL data folder** (causes path issues)
- ❌ **MySQL configuration files**

### **File Sizes to Expect:**
- **htdocs backup:** 50-500 MB (depending on your project)
- **Database backup:** 1-50 MB (depending on data size)
- **Total backup:** Usually under 1 GB

## Quick Commands Summary

### **One-Line Backup:**
```cmd
cd D:\xampp\mysql\bin && mysqldump.exe -u root ascom_db > "D:\backup\ascom_db.sql"
```

### **One-Line Restore:**
```cmd
D:\xampp\mysql\bin\mysql.exe -u root ascom_db < "D:\backup\ascom_db.sql"
```

---

**The mysqldump method is the safest and most reliable way to backup your databases before the clean reinstall.** 