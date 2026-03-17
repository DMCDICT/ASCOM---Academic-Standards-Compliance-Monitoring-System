-- Add start_date and end_date fields to school_years table
-- Run this script in your MySQL database if the fields don't exist

USE ascom_db;

-- Add start_date field if it doesn't exist
ALTER TABLE school_years 
ADD COLUMN IF NOT EXISTS start_date DATE NULL AFTER year_start;

-- Add end_date field if it doesn't exist  
ALTER TABLE school_years 
ADD COLUMN IF NOT EXISTS end_date DATE NULL AFTER year_end;

-- Update existing records to have default dates based on year_start and year_end
-- This sets start_date to August 1st of year_start and end_date to May 31st of year_end
UPDATE school_years 
SET start_date = CONCAT(year_start, '-08-01'),
    end_date = CONCAT(year_end, '-05-31')
WHERE start_date IS NULL OR end_date IS NULL;

-- Make the fields NOT NULL after updating existing records
ALTER TABLE school_years 
MODIFY COLUMN start_date DATE NOT NULL,
MODIFY COLUMN end_date DATE NOT NULL;
