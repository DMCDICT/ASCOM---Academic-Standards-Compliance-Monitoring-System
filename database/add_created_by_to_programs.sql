-- ============================================================
-- Add created_by field to programs table
-- Tracks which user (dean) created the program
-- ============================================================

SET NAMES utf8mb4;
SET FOREIGN_KEY_CHECKS = 0;

-- Add created_by column to programs
ALTER TABLE programs 
ADD COLUMN IF NOT EXISTS created_by INT DEFAULT NULL AFTER department_id,
ADD COLUMN IF NOT EXISTS created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP AFTER updated_at,
ADD COLUMN IF NOT EXISTS updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP;

-- Add foreign key constraint
-- Note: This will fail if there are existing NULL values that reference non-existent users
-- In that case, remove the FK constraint or update NULL values first

ALTER TABLE programs
ADD CONSTRAINT fk_programs_created_by 
FOREIGN KEY (created_by) REFERENCES users(id) ON DELETE SET NULL;

SET FOREIGN_KEY_CHECKS = 1;

SELECT 'Added created_by to programs table' AS status;