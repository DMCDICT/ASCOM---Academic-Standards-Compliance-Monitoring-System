# Safe Backup and Reinstall Instructions

## ❌ DON'T DO THIS:
- Copying the `ascom_db` folder directly
- Copying the entire `mysql/data` folder
- Copying MySQL configuration files

## ✅ DO THIS INSTEAD:

### **Step 1: Create Proper Backup**
```cmd
REM Create backup directories
mkdir "D:\backup\databases"
mkdir "D:\backup\htdocs"

REM Backup htdocs (your website files)
xcopy "D:\xampp\htdocs" "D:\backup\htdocs" /E /I

REM Backup database using SQL export
D:\xampp\mysql\bin\mysqldump.exe -u root ascom_db > "D:\backup\databases\ascom_db.sql"
```

### **Step 2: Verify Backup**
```cmd
REM Check if backup files exist and have content
dir "D:\backup\databases\*.sql"
dir "D:\backup\htdocs" /s
```

### **Step 3: Uninstall XAMPP**
1. Stop all XAMPP services
2. Uninstall XAMPP completely
3. Delete any remaining XAMPP folders

### **Step 4: Fresh XAMPP Installation**
1. Install XAMPP to D: drive
2. Do NOT start services yet

### **Step 5: Restore Your Data**
```cmd
REM Restore htdocs
xcopy "D:\backup\htdocs" "D:\xampp\htdocs" /E /I

REM Create database
D:\xampp\mysql\bin\mysql.exe -u root -e "CREATE DATABASE ascom_db;"

REM Import your data
D:\xampp\mysql\bin\mysql.exe -u root ascom_db < "D:\backup\databases\ascom_db.sql"
```

## Why This Works:
- ✅ **SQL backup** = Clean, portable data
- ✅ **No path issues** = Fresh MySQL installation
- ✅ **No corruption** = Proper data export/import
- ✅ **Version compatible** = Works with any MySQL version

## If Backup Fails:
If the mysqldump fails, try:
1. **Using PhpMyAdmin export** (if it's working)
2. **Manual SQL export** through PhpMyAdmin
3. **Recreate database structure** from your original SQL files

---

**The key is using SQL export/import instead of direct file copying!** 