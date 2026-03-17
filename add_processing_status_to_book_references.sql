-- Add processing_status column to book_references table
-- Run this in phpMyAdmin or MySQL client

-- Add processing_status column
ALTER TABLE book_references 
ADD COLUMN `processing_status` ENUM('processing', 'completed', 'drafted') 
DEFAULT 'processing' 
COMMENT 'Processing status: processing (no call number yet), completed (has call number), drafted (issues)';

-- Add status_reason column for draft reasons
ALTER TABLE book_references 
ADD COLUMN `status_reason` TEXT NULL 
COMMENT 'Reason for draft status (if applicable)';

-- Add index for better query performance
CREATE INDEX idx_processing_status ON book_references (processing_status);

-- Update existing records to 'completed' if they have a call number, otherwise 'processing'
UPDATE book_references 
SET processing_status = CASE 
    WHEN call_number IS NOT NULL AND call_number != '' THEN 'completed'
    ELSE 'processing'
END
WHERE processing_status IS NULL OR processing_status = 'processing';

-- Show updated table structure
DESCRIBE book_references;

