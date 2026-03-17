-- Simple SQL to add created_by and requested_by columns to book_references table
-- Run this in phpMyAdmin or MySQL

-- Add created_by column
ALTER TABLE book_references 
ADD COLUMN IF NOT EXISTS `created_by` INT(11) NULL COMMENT 'User ID of the person who created this reference';

-- Add requested_by column
ALTER TABLE book_references 
ADD COLUMN IF NOT EXISTS `requested_by` INT(11) NULL COMMENT 'User ID of the person who requested this reference';

-- Add indexes for better performance
CREATE INDEX IF NOT EXISTS idx_created_by ON book_references (created_by);
CREATE INDEX IF NOT EXISTS idx_requested_by ON book_references (requested_by);

-- Add foreign keys (optional - comment out if users table has issues)
-- ALTER TABLE book_references 
-- ADD CONSTRAINT fk_book_ref_created_by FOREIGN KEY (created_by) REFERENCES users(id) ON DELETE SET NULL;

-- ALTER TABLE book_references 
-- ADD CONSTRAINT fk_book_ref_requested_by FOREIGN KEY (requested_by) REFERENCES users(id) ON DELETE SET NULL;

-- Show the updated table structure
DESCRIBE book_references;

