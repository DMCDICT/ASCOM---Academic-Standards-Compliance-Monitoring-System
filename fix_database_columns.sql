-- fix_database_columns.sql
-- Add missing columns for Facebook-style online status

-- Add online_status column if it doesn't exist
ALTER TABLE users ADD COLUMN IF NOT EXISTS online_status ENUM('online', 'offline') DEFAULT 'offline';

-- Add last_login column if it doesn't exist
ALTER TABLE users ADD COLUMN IF NOT EXISTS last_login TIMESTAMP NULL DEFAULT NULL;

-- Add last_logout column if it doesn't exist
ALTER TABLE users ADD COLUMN IF NOT EXISTS last_logout TIMESTAMP NULL DEFAULT NULL;

-- Add last_activity column if it doesn't exist
ALTER TABLE users ADD COLUMN IF NOT EXISTS last_activity TIMESTAMP NULL DEFAULT NULL;

-- Add indexes for better performance
CREATE INDEX IF NOT EXISTS idx_users_online_status ON users(online_status);
CREATE INDEX IF NOT EXISTS idx_users_last_login ON users(last_login);
CREATE INDEX IF NOT EXISTS idx_users_last_logout ON users(last_logout);
CREATE INDEX IF NOT EXISTS idx_users_last_activity ON users(last_activity);

-- Update existing users to have default values
UPDATE users SET online_status = 'offline' WHERE online_status IS NULL;
UPDATE users SET last_activity = NOW() WHERE last_activity IS NULL;

-- Show the updated table structure
DESCRIBE users; 