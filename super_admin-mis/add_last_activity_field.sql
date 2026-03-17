-- Add last_activity field to users table for status tracking
ALTER TABLE users ADD COLUMN last_activity TIMESTAMP NULL DEFAULT NULL;

-- Add online_status field to track actual login/logout status
ALTER TABLE users ADD COLUMN online_status ENUM('online', 'offline') DEFAULT 'offline';

-- Add last_login field to track when user last logged in
ALTER TABLE users ADD COLUMN last_login TIMESTAMP NULL DEFAULT NULL;

-- Add last_logout field to track when user last logged out
ALTER TABLE users ADD COLUMN last_logout TIMESTAMP NULL DEFAULT NULL;

-- Update existing users to have a default last_activity (you can adjust this logic)
UPDATE users SET last_activity = created_at WHERE last_activity IS NULL;

-- Add index for better performance when querying by last_activity
CREATE INDEX idx_users_last_activity ON users(last_activity);

-- Add index for online status queries
CREATE INDEX idx_users_online_status ON users(online_status); 