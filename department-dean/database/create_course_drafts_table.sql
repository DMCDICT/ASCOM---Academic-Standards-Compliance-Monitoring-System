-- Create course_drafts table for saving course proposal drafts
-- This table stores draft course proposals that users can save and resume later

CREATE TABLE IF NOT EXISTS `course_drafts` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `user_id` INT NOT NULL,
    `program_id` INT NULL,
    `term` VARCHAR(50) NULL,
    `academic_year` VARCHAR(50) NULL,
    `year_level` VARCHAR(20) NULL,
    `courses_data` TEXT NOT NULL COMMENT 'JSON array of course draft data (use JSON type if MySQL 5.7.8+)',
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (`user_id`) REFERENCES `users`(`id`) ON DELETE CASCADE,
    FOREIGN KEY (`program_id`) REFERENCES `programs`(`id`) ON DELETE SET NULL,
    INDEX `idx_user_id` (`user_id`),
    INDEX `idx_program_id` (`program_id`),
    INDEX `idx_created_at` (`created_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

