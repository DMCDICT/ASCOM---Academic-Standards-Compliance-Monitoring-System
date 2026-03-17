-- Create book_references table
CREATE TABLE IF NOT EXISTS `book_references` (
  `id` INT(11) NOT NULL AUTO_INCREMENT,
  `course_id` INT(11) NOT NULL,
  `title` VARCHAR(255) NOT NULL,
  `isbn` VARCHAR(20) NULL,
  `publisher` VARCHAR(150) NULL,
  `copyright_year` VARCHAR(10) NULL,
  `edition` VARCHAR(50) NULL,
  `location` VARCHAR(255) NULL,
  `call_number` VARCHAR(100) NULL,
  `created_by` INT(11) NULL COMMENT 'User ID of the person who created this reference',
  `requested_by` INT(11) NULL COMMENT 'User ID of the person who requested this reference',
  `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_course_id` (`course_id`),
  KEY `idx_created_by` (`created_by`),
  KEY `idx_requested_by` (`requested_by`),
  CONSTRAINT `fk_book_ref_course` FOREIGN KEY (`course_id`) REFERENCES `courses` (`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_book_ref_created_by` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  CONSTRAINT `fk_book_ref_requested_by` FOREIGN KEY (`requested_by`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci COMMENT='Stores book references for courses';

