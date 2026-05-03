<?php
// Create material_requests table for tracking material requests from deans to librarians
// This will be created automatically when the first request is made via the API

// The table will be created with this structure:
// - id: Primary key
// - course_id: Foreign key to courses table
// - requested_by: User ID of dean
// - book_title, author, isbn, publisher, edition, publication_year: Book details
// - justification: Why the book is needed
// - status: pending, processing, completed, rejected
// - librarian_notes: Notes from librarian
// - processed_by: Librarian who processed
// - processed_at: When processed
// - created_at, updated_at: Timestamps

echo "The material_requests table will be created automatically when the first request is submitted.\n";
echo "Alternatively, you can create it manually using the SQL below:\n\n";

$sql = "CREATE TABLE IF NOT EXISTS `material_requests` (
    `id` INT(11) NOT NULL AUTO_INCREMENT,
    `course_id` INT(11) NOT NULL COMMENT 'Course being requested for',
    `requested_by` INT(11) NULL COMMENT 'User ID of dean requesting',
    `book_title` VARCHAR(500) NOT NULL COMMENT 'Requested book title',
    `author` VARCHAR(255) NULL COMMENT 'Book author',
    `isbn` VARCHAR(50) NULL COMMENT 'Book ISBN',
    `publisher` VARCHAR(255) NULL COMMENT 'Book publisher',
    `edition` VARCHAR(100) NULL COMMENT 'Book edition',
    `publication_year` YEAR NULL COMMENT 'Publication year',
    `justification` TEXT NULL COMMENT 'Why this book is needed',
    `status` ENUM('pending', 'processing', 'completed', 'rejected') NOT NULL DEFAULT 'pending' COMMENT 'Request status',
    `librarian_notes` TEXT NULL COMMENT 'Notes from librarian',
    `processed_by` INT(11) NULL COMMENT 'Librarian who processed',
    `processed_at` DATETIME NULL COMMENT 'When it was processed',
    `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    KEY `idx_course_id` (`course_id`),
    KEY `idx_requested_by` (`requested_by`),
    KEY `idx_status` (`status`),
    KEY `idx_created_at` (`created_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci COMMENT='Material requests from deans to librarians';";

echo $sql . "\n";