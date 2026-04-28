-- ============================================================
-- Enhance course_assignments table for syllabus tracking
-- Adds syllabus submission status per teacher-course assignment
-- ============================================================

SET NAMES utf8mb4;
SET FOREIGN_KEY_CHECKS = 0;

-- Add syllabus tracking columns to course_assignments
ALTER TABLE course_assignments
ADD COLUMN IF NOT EXISTS syllabus_status ENUM('not_started', 'in_progress', 'submitted', 'ph_approved', 'dean_approved', 'needs_revision') DEFAULT 'not_started' AFTER is_active,
ADD COLUMN IF NOT EXISTS syllabus_submitted_at DATETIME DEFAULT NULL AFTER syllabus_status,
ADD COLUMN IF NOT EXISTS ph_approved_at DATETIME DEFAULT NULL AFTER syllabus_submitted_at,
ADD COLUMN IF NOT EXISTS dean_approved_at DATETIME DEFAULT NULL AFTER ph_approved_at,
ADD COLUMN IF NOT EXISTS current_role ENUM('teacher', 'program_head') DEFAULT 'teacher' AFTER assigned_at;

SET FOREIGN_KEY_CHECKS = 1;

SELECT 'Enhanced course_assignments with syllabus tracking' AS status;