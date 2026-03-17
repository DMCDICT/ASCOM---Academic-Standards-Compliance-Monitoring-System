-- Add pending status support to courses table
-- This allows librarians to create courses that require dean approval

-- First, modify the courses table to support pending status
ALTER TABLE courses 
MODIFY COLUMN status ENUM('pending', 'active', 'inactive', 'rejected') 
DEFAULT 'active' 
COMMENT 'Course status: pending (awaiting approval), active (approved), inactive, rejected';

-- Add created_by_user_id to track who created the course
ALTER TABLE courses 
ADD COLUMN `created_by_user_id` INT NULL 
COMMENT 'ID of the user who created this course',
ADD COLUMN `created_by_role` VARCHAR(50) NULL 
COMMENT 'Role of the user who created this course (dean, librarian, etc.)';

-- Add indexes for better query performance on status and creator
CREATE INDEX idx_course_status ON courses (status);
CREATE INDEX idx_created_by_user ON courses (created_by_user_id);

-- Show updated table structure
DESCRIBE courses;

