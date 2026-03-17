<?php
// Script to create course_programs table and migrate existing data
error_reporting(E_ALL);
ini_set('display_errors', 1);

try {
    require_once '../includes/db_connection.php';
    echo "✅ Database connection successful\n\n";
    
    // Check if course_programs table already exists
    $stmt = $pdo->query("SHOW TABLES LIKE 'course_programs'");
    if ($stmt->rowCount() > 0) {
        echo "✅ course_programs table already exists\n";
    } else {
        echo "=== CREATING course_programs TABLE ===\n";
        
        // Create course_programs table
        $createTableSQL = "
            CREATE TABLE course_programs (
                id INT AUTO_INCREMENT PRIMARY KEY,
                course_code VARCHAR(20) NOT NULL,
                program_id INT NOT NULL,
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                UNIQUE KEY unique_course_program (course_code, program_id),
                FOREIGN KEY (program_id) REFERENCES programs(id) ON DELETE CASCADE
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
        ";
        
        $pdo->exec($createTableSQL);
        echo "✅ course_programs table created successfully\n";
    }
    
    // Check if courses table has program_id column
    echo "\n=== CHECKING COURSES TABLE STRUCTURE ===\n";
    $stmt = $pdo->query("DESCRIBE courses");
    $columns = $stmt->fetchAll(PDO::FETCH_ASSOC);
    $hasProgramId = false;
    
    foreach ($columns as $column) {
        echo "- {$column['Field']}: {$column['Type']}\n";
        if ($column['Field'] === 'program_id') {
            $hasProgramId = true;
        }
    }
    
    if ($hasProgramId) {
        echo "\n=== MIGRATING EXISTING COURSE-PROGRAM RELATIONSHIPS ===\n";
        
        // Get all courses with their program_id
        $stmt = $pdo->query("SELECT course_code, program_id FROM courses WHERE program_id IS NOT NULL");
        $courses = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        $migrated = 0;
        foreach ($courses as $course) {
            try {
                // Insert into course_programs table
                $insertSQL = "INSERT IGNORE INTO course_programs (course_code, program_id) VALUES (?, ?)";
                $stmt = $pdo->prepare($insertSQL);
                $stmt->execute([$course['course_code'], $course['program_id']]);
                
                if ($stmt->rowCount() > 0) {
                    $migrated++;
                    echo "✅ Migrated: {$course['course_code']} -> Program ID {$course['program_id']}\n";
                }
            } catch (Exception $e) {
                echo "❌ Error migrating {$course['course_code']}: " . $e->getMessage() . "\n";
            }
        }
        
        echo "\n✅ Migrated $migrated course-program relationships\n";
    } else {
        echo "\n⚠️  No program_id column found in courses table - skipping migration\n";
    }
    
    // Show current course_programs data
    echo "\n=== CURRENT course_programs DATA ===\n";
    $stmt = $pdo->query("
        SELECT 
            cp.course_code, 
            p.program_code, 
            p.program_name,
            COUNT(*) as count
        FROM course_programs cp
        JOIN programs p ON cp.program_id = p.id
        GROUP BY cp.course_code, p.program_code
        ORDER BY cp.course_code
    ");
    
    $associations = $stmt->fetchAll(PDO::FETCH_ASSOC);
    if ($associations) {
        foreach ($associations as $assoc) {
            echo "- {$assoc['course_code']}: {$assoc['program_code']} - {$assoc['program_name']}\n";
        }
    } else {
        echo "No course-program associations found\n";
    }
    
    echo "\n✅ Course programs table setup completed successfully!\n";
    
} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
    echo "Stack trace: " . $e->getTraceAsString() . "\n";
}
?>
