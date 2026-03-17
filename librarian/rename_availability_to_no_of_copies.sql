-- Change availability column to no_of_copies for better clarity
-- Run this in phpMyAdmin or MySQL client

-- Rename the column from availability to no_of_copies
ALTER TABLE book_references CHANGE COLUMN `availability` `no_of_copies` INT(11) DEFAULT 1;

-- Show updated table structure
DESCRIBE book_references;
