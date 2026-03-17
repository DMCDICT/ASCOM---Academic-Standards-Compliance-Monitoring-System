-- Add title field to users table for academic titles
ALTER TABLE users ADD COLUMN title VARCHAR(50) DEFAULT NULL AFTER last_name;

-- Update existing users to have no title by default
UPDATE users SET title = NULL WHERE title IS NULL;
