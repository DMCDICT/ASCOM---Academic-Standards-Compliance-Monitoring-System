-- Update school year active status based on current year (2025)
-- This script will set the correct active status for all school years

USE ascom_db;

-- First, let's see the current status
SELECT 
    id,
    year_start,
    year_end,
    is_active,
    CASE 
        WHEN is_active = 1 THEN 'Active'
        ELSE 'Inactive'
    END as current_status
FROM school_years 
ORDER BY year_start DESC;

-- Update all school years to set correct active status
-- School year is active if current date (2025-01-27) is between start_date and end_date (inclusive)
UPDATE school_years 
SET is_active = CASE 
    WHEN '2025-01-27' >= start_date AND '2025-01-27' <= end_date THEN 1
    ELSE 0
END;

-- Verify the changes
SELECT 
    id,
    year_start,
    year_end,
    is_active,
    CASE 
        WHEN is_active = 1 THEN 'Active'
        ELSE 'Inactive'
    END as new_status,
    CASE 
        WHEN '2025-01-27' >= start_date AND '2025-01-27' <= end_date THEN 'Should be Active'
        ELSE 'Should be Inactive'
    END as expected_status
FROM school_years 
ORDER BY year_start DESC;
