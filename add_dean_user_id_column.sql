-- Add dean_user_id column to departments table if it doesn't exist
-- This column will store the user ID of the assigned department dean

USE ascom_db;

-- Check if dean_user_id column exists, if not add it
SET @sql = (SELECT IF(
    (SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS 
     WHERE TABLE_SCHEMA = 'ascom_db' 
     AND TABLE_NAME = 'departments' 
     AND COLUMN_NAME = 'dean_user_id') = 0,
    'ALTER TABLE departments ADD COLUMN dean_user_id INT NULL AFTER color_code',
    'SELECT "dean_user_id column already exists" as message'
));

PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

-- Add foreign key constraint to link dean_user_id to users table
-- Only if the column was just added (we'll handle this manually if needed)

-- Show the updated table structure
DESCRIBE departments; 