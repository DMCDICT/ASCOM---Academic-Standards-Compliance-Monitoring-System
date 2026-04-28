-- Add missing color_code column to programs table
-- Run this SQL directly in phpMyAdmin or MySQL CLI

-- Check if column exists first, if not add it
ALTER TABLE programs ADD COLUMN color_code VARCHAR(7) NOT NULL DEFAULT '#00674b' AFTER created_by;

-- Update existing programs with their department's color
UPDATE programs p
JOIN departments d ON p.department_id = d.id
SET p.color_code = COALESCE(d.color_code, '#00674b')
WHERE p.color_code = '#00674b' OR p.color_code IS NULL OR p.color_code = '';