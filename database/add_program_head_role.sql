-- ============================================================
-- Add program_head role if not exists
-- Ensures the program_head role exists in roles table
-- ============================================================

SET NAMES utf8mb4;

-- Insert program_head role if not exists
INSERT IGNORE INTO roles (role_name, role_description) 
VALUES ('program_head', 'Program Head - Manages programs and courses, reviews teacher syllabi');

SELECT 'Added program_head role' AS status;