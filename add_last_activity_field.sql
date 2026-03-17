-- add_last_activity_field.sql
-- SQL script to add last_activity column to users table

-- Check if last_activity column exists, if not add it
ALTER TABLE users ADD COLUMN IF NOT EXISTS last_activity TIMESTAMP NULL DEFAULT NULL;

-- Add index for better performance on activity queries
CREATE INDEX IF NOT EXISTS idx_users_last_activity ON users(last_activity);

-- Update existing users to have a default last_activity (set to current time)
UPDATE users SET last_activity = NOW() WHERE last_activity IS NULL AND is_active = 1;

-- Show the updated table structure
DESCRIBE users; 