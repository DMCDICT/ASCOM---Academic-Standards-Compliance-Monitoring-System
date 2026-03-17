<?php
// create_course_drafts_table.php
// Script to create the course_drafts table

header('Content-Type: text/plain');

try {
    require_once dirname(__FILE__) . '/../includes/db_connection.php';
    
    echo "=== Creating Course Drafts Table ===\n\n";
    
    // Check if course_drafts table already exists
    $checkQuery = "SHOW TABLES LIKE 'course_drafts'";
    $stmt = $pdo->prepare($checkQuery);
    $stmt->execute();
    $tableExists = $stmt->rowCount() > 0;
    
    if ($tableExists) {
        echo "✅ Course drafts table already exists!\n";
        echo "Checking table structure...\n\n";
    } else {
        echo "Creating course_drafts table...\n\n";
        
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
        
        // Create the course_drafts table
        $createTableQuery = "
            CREATE TABLE `course_drafts` (
                `id` INT AUTO_INCREMENT PRIMARY KEY,
                `user_id` INT NOT NULL,
                `program_id` INT NULL,
                `term` VARCHAR(50) NULL,
                `academic_year` VARCHAR(50) NULL,
                `year_level` VARCHAR(20) NULL,
                `courses_data` $coursesDataType NOT NULL COMMENT 'JSON array of course draft data',
                `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                FOREIGN KEY (`user_id`) REFERENCES `users`(`id`) ON DELETE CASCADE,
                FOREIGN KEY (`program_id`) REFERENCES `programs`(`id`) ON DELETE SET NULL,
                INDEX `idx_user_id` (`user_id`),
                INDEX `idx_program_id` (`program_id`),
                INDEX `idx_created_at` (`created_at`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
        ";
        
        $pdo->exec($createTableQuery);
        echo "✅ Course drafts table created successfully!\n\n";
    }
    
    // Show the table structure
    echo "Course drafts table structure:\n";
    echo "==============================\n";
    $describeQuery = "DESCRIBE course_drafts";
    $stmt = $pdo->prepare($describeQuery);
    $stmt->execute();
    $columns = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    foreach ($columns as $column) {
        echo sprintf(
            "%-20s %-20s %-10s %-10s %-10s %-10s\n",
            $column['Field'],
            $column['Type'],
            $column['Null'],
            $column['Key'],
            $column['Default'] ?? 'NULL',
            $column['Extra']
        );
    }
    
    echo "\n✅ Course drafts table setup complete!\n";
    
} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
    echo "\nStack trace:\n" . $e->getTraceAsString() . "\n";
}

