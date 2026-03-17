-- Reset/Erase Courses and Book References Tables
-- This script will DELETE all data from book_references and courses tables
-- 
-- WARNING: This will permanently delete all courses and book references!
-- Use with caution!
--
-- Run this in phpMyAdmin SQL tab or MySQL command line

-- Use your database (replace 'ascom_db' with your actual database name if different)
USE ascom_db;

-- Disable foreign key checks temporarily
SET FOREIGN_KEY_CHECKS = 0;

-- Delete all book references
DELETE FROM book_references;

-- Delete all courses
DELETE FROM courses;

-- Reset AUTO_INCREMENT counters
ALTER TABLE book_references AUTO_INCREMENT = 1;
ALTER TABLE courses AUTO_INCREMENT = 1;

-- Re-enable foreign key checks
SET FOREIGN_KEY_CHECKS = 1;

-- Verify deletion (optional - shows 0 records if successful)
SELECT 
    'book_references' AS table_name, 
    COUNT(*) AS remaining_records 
FROM book_references
UNION ALL
SELECT 
    'courses' AS table_name, 
    COUNT(*) AS remaining_records 
FROM courses;


