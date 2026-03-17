<?php
// create_course_proposals_table.php
// Script to create the course_proposals table

header('Content-Type: text/plain');

try {
    require_once dirname(__FILE__) . '/../includes/db_connection.php';
    
    echo "=== Creating Course Proposals Table ===\n\n";
    
    // Check if course_proposals table already exists
    $checkQuery = "SHOW TABLES LIKE 'course_proposals'";
    $stmt = $pdo->prepare($checkQuery);
    $stmt->execute();
    $tableExists = $stmt->rowCount() > 0;
    
    if ($tableExists) {
        echo "✅ Course proposals table already exists!\n";
        echo "Checking table structure...\n\n";
    } else {
        echo "Creating course_proposals table...\n\n";
        
        // Check MySQL version for JSON support (MySQL 5.7.8+)
        $versionQuery = "SELECT VERSION() as version";
        $versionStmt = $pdo->query($versionQuery);
        $version = $versionStmt->fetch(PDO::FETCH_ASSOC)['version'];
        $versionParts = explode('.', $version);
        $majorVersion = (int)$versionParts[0];
        $minorVersion = (int)$versionParts[1];
        $patchVersion = isset($versionParts[2]) ? (int)explode('-', $versionParts[2])[0] : 0;
        
        // Use JSON if MySQL 5.7.8+, otherwise use TEXT
        $coursesDataType = 'TEXT';
        if ($majorVersion > 5 || ($majorVersion == 5 && $minorVersion > 7) || 
            ($majorVersion == 5 && $minorVersion == 7 && $patchVersion >= 8)) {
            $coursesDataType = 'JSON';
            echo "✅ MySQL version $version supports JSON column type\n";
        } else {
            echo "⚠️  MySQL version $version - using TEXT instead of JSON\n";
        }
        
        // Create the course_proposals table
        $createTableQuery = "
            CREATE TABLE `course_proposals` (
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
                `courses_data` $coursesDataType NOT NULL COMMENT 'JSON array of course proposal data',
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
        ";
        
        $pdo->exec($createTableQuery);
        echo "✅ Course proposals table created successfully!\n\n";
    }
    
    // Show the table structure
    echo "Course proposals table structure:\n";
    echo "==================================\n";
    $describeQuery = "DESCRIBE course_proposals";
    $stmt = $pdo->prepare($describeQuery);
    $stmt->execute();
    $columns = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    foreach ($columns as $column) {
        echo sprintf(
            "%-20s %-30s %-10s %-10s %-15s %-10s\n",
            $column['Field'],
            $column['Type'],
            $column['Null'],
            $column['Key'],
            $column['Default'] ?? 'NULL',
            $column['Extra']
        );
    }
    
    echo "\n✅ Course proposals table setup complete!\n";
    
} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
    echo "\nStack trace:\n" . $e->getTraceAsString() . "\n";
}

