-- ============================================================
-- Add created_by and ownership fields to courses table
-- Tracks which user (dean) created the course
-- Also adds syllabus-related status fields
-- ============================================================

SET NAMES utf8mb4;
SET FOREIGN_KEY_CHECKS = 0;

-- Add ownership and status columns to courses
ALTER TABLE courses 
ADD COLUMN IF NOT EXISTS created_by INT DEFAULT NULL AFTER faculty_id,
ADD COLUMN IF NOT EXISTS owner_type ENUM('dean', 'program_head') DEFAULT 'dean' AFTER created_by,
ADD COLUMN IF NOT EXISTS syllabus_status ENUM('not_started', 'in_progress', 'submitted', 'approved', 'needs_revision') DEFAULT 'not_started' AFTER status,
ADD COLUMN IF NOT EXISTS last_syllabus_update DATETIME DEFAULT NULL AFTER updated_at;

-- Add foreign key for created_by
ALTER TABLE courses
ADD CONSTRAINT fk_courses_created_by 
FOREIGN KEY (created_by) REFERENCES users(id) ON DELETE SET NULL;

SET FOREIGN_KEY_CHECKS = 1;

SELECT 'Added ownership and syllabus status to courses table' AS status;