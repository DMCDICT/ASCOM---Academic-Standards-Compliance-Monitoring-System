-- Create book_requests table
-- Tracks library book requests from dean for department compliance

CREATE TABLE IF NOT EXISTS `book_requests` (
  `id` INT(11) NOT NULL AUTO_INCREMENT,
  `requesting_dean_id` INT(11) NOT NULL COMMENT 'User ID of the dean who requested',
  `department_code` VARCHAR(50) NOT NULL COMMENT 'Department code making the request',
  `book_title` VARCHAR(255) NOT NULL COMMENT 'Title of requested book',
  `author` VARCHAR(255) NULL COMMENT 'Author of the book',
  `isbn` VARCHAR(30) NULL COMMENT 'ISBN if provided',
  `publisher` VARCHAR(150) NULL COMMENT 'Publisher name',
  `publication_year` VARCHAR(10) NULL COMMENT 'Publication year',
  `edition` VARCHAR(50) NULL COMMENT 'Edition if specified',
  `reason` TEXT NULL COMMENT 'Reason why this book is needed for compliance',
  `status` ENUM('PENDING', 'DONE') NOT NULL DEFAULT 'PENDING' COMMENT 'Request status',
  `requested_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT 'When request was made',
  `processed_by` INT(11) NULL COMMENT 'User ID of librarian who processed',
  `processed_at` TIMESTAMP NULL COMMENT 'When request was processed',
  PRIMARY KEY (`id`),
  KEY `idx_requesting_dean_id` (`requesting_dean_id`),
  KEY `idx_department_code` (`department_code`),
  KEY `idx_status` (`status`),
  KEY `idx_requested_at` (`requested_at`),
  CONSTRAINT `fk_book_request_dean` FOREIGN KEY (`requesting_dean_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_book_request_processed_by` FOREIGN KEY (`processed_by`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci COMMENT='Tracks library book requests from deans for department compliance';