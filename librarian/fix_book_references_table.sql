-- Add missing columns to book_references table
-- Run this in phpMyAdmin or MySQL client

-- Add no_of_copies column
ALTER TABLE book_references ADD COLUMN `no_of_copies` INT(11) DEFAULT 1 COMMENT 'Number of copies available';

-- Add book_title column (alternative to title)
ALTER TABLE book_references ADD COLUMN `book_title` VARCHAR(255) COMMENT 'Book title (alternative to title)';

-- Add copyright column (alternative to copyright_year)
ALTER TABLE book_references ADD COLUMN `copyright` VARCHAR(10) COMMENT 'Copyright year (alternative to copyright_year)';

-- Add authors column
ALTER TABLE book_references ADD COLUMN `authors` VARCHAR(255) COMMENT 'Book authors';

-- Show updated table structure
DESCRIBE book_references;
