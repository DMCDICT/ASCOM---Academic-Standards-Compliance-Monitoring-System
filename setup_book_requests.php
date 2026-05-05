<?php
/**
 * Setup script to create the book_requests table
 * Run this once to set up the database for the book request system
 */

require_once __DIR__ . '/bootstrap/database.php';

echo "Setting up book_requests table...\n";

try {
    $pdo = ascom_get_pdo();
    
    // Check if table already exists
    $tableCheck = $pdo->query("SHOW TABLES LIKE 'book_requests'");
    if ($tableCheck->rowCount() > 0) {
        echo "Table 'book_requests' already exists.\n";
    } else {
        // Create the table
        $sql = "
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
          `status` ENUM('PENDING', 'APPROVED', 'REJECTED') NOT NULL DEFAULT 'PENDING' COMMENT 'Request status',
          `requested_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT 'When request was made',
          `processed_by` INT(11) NULL COMMENT 'User ID of librarian who processed',
          `processed_at` TIMESTAMP NULL COMMENT 'When request was processed',
          `rejection_reason` TEXT NULL COMMENT 'Reason for rejection if rejected',
          PRIMARY KEY (`id`),
          KEY `idx_requesting_dean_id` (`requesting_dean_id`),
          KEY `idx_department_code` (`department_code`),
          KEY `idx_status` (`status`),
          KEY `idx_requested_at` (`requested_at`),
          CONSTRAINT `fk_book_request_dean` FOREIGN KEY (`requesting_dean_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
          CONSTRAINT `fk_book_request_processed_by` FOREIGN KEY (`processed_by`) REFERENCES `users` (`id`) ON DELETE SET NULL
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci COMMENT='Tracks library book requests from deans for department compliance'
        ";
        
        $pdo->exec($sql);
        echo "Table 'book_requests' created successfully!\n";
    }
    
    echo "\nSetup complete! The book request system is ready to use.\n";
    echo "\nTo test:\n";
    echo "1. Log in as a Dean -> Go to 'Library Book Requests' page\n";
    echo "2. Log in as a Librarian -> Go to 'Book Requests' page\n";
    
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}