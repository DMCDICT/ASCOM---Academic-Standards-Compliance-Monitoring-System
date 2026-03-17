-- Create library_books table for librarian records
CREATE TABLE IF NOT EXISTS `library_books` (
  `id` INT(11) NOT NULL AUTO_INCREMENT,
  `call_number` VARCHAR(100) NOT NULL,
  `isbn` VARCHAR(20) NULL,
  `title` VARCHAR(255) NOT NULL,
  `authors` TEXT NOT NULL,
  `publisher` VARCHAR(150) NULL,
  `copyright_year` YEAR NULL,
  `edition` VARCHAR(50) NULL,
  `no_of_copies` INT(11) NOT NULL DEFAULT 1,
  `available_copies` INT(11) NOT NULL DEFAULT 1,
  `location` VARCHAR(255) NULL,
  `subject_category` VARCHAR(100) NULL,
  `description` TEXT NULL,
  `keywords` TEXT NULL,
  `created_by` INT(11) NULL COMMENT 'Librarian who added this book',
  `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `unique_call_number` (`call_number`),
  KEY `idx_title` (`title`),
  KEY `idx_authors` (`authors`(100)),
  KEY `idx_subject_category` (`subject_category`),
  KEY `idx_created_by` (`created_by`),
  CONSTRAINT `fk_library_books_created_by` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci COMMENT='Library books catalog managed by librarians';

-- Create index for full-text search
ALTER TABLE `library_books` ADD FULLTEXT(`title`, `authors`, `description`, `keywords`);
