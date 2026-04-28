-- ============================================================
-- ASCOM Course Syllabus System - Complete Migration Script
-- Run this script to add all syllabus-related tables and fields
-- ============================================================

SET NAMES utf8mb4;
SET FOREIGN_KEY_CHECKS = 0;

-- ============================================================
-- 1. Add program_head role if not exists
-- ============================================================
INSERT IGNORE INTO roles (role_name, role_description) 
VALUES ('program_head', 'Program Head - Manages programs and courses, reviews teacher syllabi');

-- ============================================================
-- 2. Add created_by column to programs table
-- ============================================================
SET @sql = (SELECT IF(
    (SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS 
     WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'programs' AND COLUMN_NAME = 'created_by') = 0,
    'ALTER TABLE programs ADD COLUMN created_by INT DEFAULT NULL AFTER department_id',
    'SELECT 1'
));
PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

-- Add foreign key constraint (ignore if already exists)
-- ALTER TABLE programs ADD CONSTRAINT fk_programs_created_by FOREIGN KEY (created_by) REFERENCES users(id) ON DELETE SET NULL;

-- ============================================================
-- 3. Add ownership and syllabus status columns to courses table
-- ============================================================
SET @sql = (SELECT IF(
    (SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS 
     WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'courses' AND COLUMN_NAME = 'created_by') = 0,
    'ALTER TABLE courses ADD COLUMN created_by INT DEFAULT NULL AFTER faculty_id',
    'SELECT 1'
));
PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

SET @sql = (SELECT IF(
    (SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS 
     WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'courses' AND COLUMN_NAME = 'owner_type') = 0,
    'ALTER TABLE courses ADD COLUMN owner_type ENUM(''dean'', ''program_head'') DEFAULT ''dean'' AFTER created_by',
    'SELECT 1'
));
PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

SET @sql = (SELECT IF(
    (SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS 
     WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'courses' AND COLUMN_NAME = 'syllabus_status') = 0,
    'ALTER TABLE courses ADD COLUMN syllabus_status ENUM(''not_started'', ''in_progress'', ''submitted'', ''approved'', ''needs_revision'') DEFAULT ''not_started'' AFTER status',
    'SELECT 1'
));
PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

SET @sql = (SELECT IF(
    (SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS 
     WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'courses' AND COLUMN_NAME = 'last_syllabus_update') = 0,
    'ALTER TABLE courses ADD COLUMN last_syllabus_update DATETIME DEFAULT NULL AFTER updated_at',
    'SELECT 1'
));
PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

-- ============================================================
-- 4. Add syllabus tracking columns to course_assignments
-- ============================================================
SET @sql = (SELECT IF(
    (SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS 
     WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'course_assignments' AND COLUMN_NAME = 'syllabus_status') = 0,
    'ALTER TABLE course_assignments ADD COLUMN syllabus_status ENUM(''not_started'', ''in_progress'', ''submitted'', ''ph_approved'', ''dean_approved'', ''needs_revision'') DEFAULT ''not_started'' AFTER is_active',
    'SELECT 1'
));
PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

SET @sql = (SELECT IF(
    (SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS 
     WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'course_assignments' AND COLUMN_NAME = 'syllabus_submitted_at') = 0,
    'ALTER TABLE course_assignments ADD COLUMN syllabus_submitted_at DATETIME DEFAULT NULL AFTER syllabus_status',
    'SELECT 1'
));
PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

SET @sql = (SELECT IF(
    (SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS 
     WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'course_assignments' AND COLUMN_NAME = 'ph_approved_at') = 0,
    'ALTER TABLE course_assignments ADD COLUMN ph_approved_at DATETIME DEFAULT NULL AFTER syllabus_submitted_at',
    'SELECT 1'
));
PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

SET @sql = (SELECT IF(
    (SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS 
     WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'course_assignments' AND COLUMN_NAME = 'dean_approved_at') = 0,
    'ALTER TABLE course_assignments ADD COLUMN dean_approved_at DATETIME DEFAULT NULL AFTER ph_approved_at',
    'SELECT 1'
));
PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

SET @sql = (SELECT IF(
    (SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS 
     WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'course_assignments' AND COLUMN_NAME = 'current_role') = 0,
    'ALTER TABLE course_assignments ADD COLUMN current_role ENUM(''teacher'', ''program_head'') DEFAULT ''teacher'' AFTER assigned_at',
    'SELECT 1'
));
PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

-- ============================================================
-- 5. Create course_syllabi table
-- ============================================================
DROP TABLE IF EXISTS course_syllabi;

CREATE TABLE course_syllabi (
    id INT AUTO_INCREMENT PRIMARY KEY,
    course_id INT NOT NULL,
    teacher_id INT NOT NULL,
    academic_year VARCHAR(50) DEFAULT NULL,
    term VARCHAR(50) DEFAULT NULL,
    
    -- Course Overview
    course_description TEXT DEFAULT NULL,
    course_objectives TEXT DEFAULT NULL,
    expected_course_outcomes TEXT DEFAULT NULL,
    
    -- Resources
    books TEXT DEFAULT NULL,
    ebooks TEXT DEFAULT NULL,
    web_resources TEXT DEFAULT NULL,
    
    -- Learning Plan and Exams
    learning_plan TEXT DEFAULT NULL,
    exam_schedules TEXT DEFAULT NULL,
    
    -- Grading and Policies
    grading_system TEXT DEFAULT NULL,
    course_requirements TEXT DEFAULT NULL,
    course_expectations TEXT DEFAULT NULL,
    remote_policies TEXT DEFAULT NULL,
    references TEXT DEFAULT NULL,
    
    -- Status tracking
    status ENUM('draft', 'submitted', 'ph_review', 'ph_approved', 'dean_approved', 'revision_requested') DEFAULT 'draft',
    
    -- Timestamps
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    submitted_at DATETIME DEFAULT NULL,
    ph_approved_at DATETIME DEFAULT NULL,
    dean_approved_at DATETIME DEFAULT NULL,
    
    -- Review tracking
    ph_reviewer_id INT DEFAULT NULL,
    ph_review_comments TEXT DEFAULT NULL,
    dean_reviewer_id INT DEFAULT NULL,
    dean_review_comments TEXT DEFAULT NULL,
    
    -- Revision tracking
    revision_count INT DEFAULT 0,
    last_revision_note TEXT DEFAULT NULL,
    
    -- Foreign keys
    FOREIGN KEY (course_id) REFERENCES courses(id) ON DELETE CASCADE,
    FOREIGN KEY (teacher_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (ph_reviewer_id) REFERENCES users(id) ON DELETE SET NULL,
    FOREIGN KEY (dean_reviewer_id) REFERENCES users(id) ON DELETE SET NULL,
    
    -- Ensure one syllabus per course per teacher per term
    UNIQUE KEY unique_course_teacher_term (course_id, teacher_id, academic_year, term),
    
    INDEX idx_course (course_id),
    INDEX idx_teacher (teacher_id),
    INDEX idx_status (status)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Re-enable foreign key checks
SET FOREIGN_KEY_CHECKS = 1;

-- ============================================================
-- Migration Complete
-- ============================================================
SELECT 'Syllabus migration completed successfully!' AS status;
SELECT 'Tables created: course_syllabi' AS tables_created;
SELECT 'Columns added to courses: created_by, owner_type, syllabus_status, last_syllabus_update' AS courses_columns;
SELECT 'Columns added to course_assignments: syllabus_status, syllabus_submitted_at, ph_approved_at, dean_approved_at, current_role' AS assignments_columns;
SELECT 'Columns added to programs: created_by' AS programs_columns;