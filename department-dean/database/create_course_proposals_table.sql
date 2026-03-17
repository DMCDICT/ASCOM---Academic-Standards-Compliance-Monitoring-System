-- Create course_proposals table for submitted course proposals
-- This table stores submitted course proposals with status tracking (Pending, Approved, Rejected, etc.)

CREATE TABLE IF NOT EXISTS `course_proposals` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `user_id` INT NOT NULL COMMENT 'ID of the user who submitted the proposal',
    `program_id` INT NULL,
    `term` VARCHAR(50) NULL,
    `academic_year` VARCHAR(50) NULL,
    `year_level` VARCHAR(20) NULL,
    `course_type` VARCHAR(50) NULL COMMENT 'New Course Proposal, Course Revision, Cross-Department',
    `status` ENUM('Draft', 'Pending QA Review', 'Under Review', 'Approved', 'Rejected', 'Added to Program') 
        DEFAULT 'Pending QA Review' 
        COMMENT 'Proposal status',
    `courses_data` TEXT NOT NULL COMMENT 'JSON array of course proposal data',
    `submitted_at` TIMESTAMP NULL COMMENT 'When the proposal was submitted to QA',
    `reviewed_at` TIMESTAMP NULL COMMENT 'When the proposal was reviewed',
    `reviewed_by` INT NULL COMMENT 'ID of the user who reviewed the proposal',
    `review_notes` TEXT NULL COMMENT 'Review comments or notes',
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (`user_id`) REFERENCES `users`(`id`) ON DELETE CASCADE,
    FOREIGN KEY (`program_id`) REFERENCES `programs`(`id`) ON DELETE SET NULL,
    FOREIGN KEY (`reviewed_by`) REFERENCES `users`(`id`) ON DELETE SET NULL,
    INDEX `idx_user_id` (`user_id`),
    INDEX `idx_program_id` (`program_id`),
    INDEX `idx_status` (`status`),
    INDEX `idx_submitted_at` (`submitted_at`),
    INDEX `idx_created_at` (`created_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

