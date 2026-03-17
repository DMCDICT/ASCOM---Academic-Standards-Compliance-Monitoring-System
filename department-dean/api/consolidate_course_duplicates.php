<?php
// Script to consolidate duplicate course entries and create proper many-to-many relationships
error_reporting(E_ALL);
ini_set('display_errors', 1);

try {
    require_once '../includes/db_connection.php';
    echo "✅ Database connection successful\n\n";
    
    // Step 1: Create course_programs table if it doesn't exist
    echo "=== STEP 1: CREATING course_programs TABLE ===\n";
    $stmt = $pdo->query("SHOW TABLES LIKE 'course_programs'");
    if ($stmt->rowCount() > 0) {
        echo "✅ course_programs table already exists\n";
    } else {
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
        echo "✅ course_programs table created\n";
    }
    
    // Step 2: Find duplicate course entries
    echo "\n=== STEP 2: FINDING DUPLICATE COURSE ENTRIES ===\n";
    $duplicateQuery = "
        SELECT 
            course_code, 
            COUNT(*) as count,
            GROUP_CONCAT(id) as course_ids,
            GROUP_CONCAT(program_id) as program_ids
        FROM courses 
        GROUP BY course_code 
        HAVING COUNT(*) > 1
        ORDER BY course_code
    ";
    
    $stmt = $pdo->query($duplicateQuery);
    $duplicates = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    if (empty($duplicates)) {
        echo "✅ No duplicate course entries found\n";
    } else {
        echo "Found " . count($duplicates) . " courses with duplicates:\n";
        foreach ($duplicates as $duplicate) {
            echo "- {$duplicate['course_code']}: {$duplicate['count']} entries (IDs: {$duplicate['course_ids']}, Programs: {$duplicate['program_ids']})\n";
        }
    }
    
    // Step 3: Consolidate duplicates
    if (!empty($duplicates)) {
        echo "\n=== STEP 3: CONSOLIDATING DUPLICATES ===\n";
        
        foreach ($duplicates as $duplicate) {
            $courseCode = $duplicate['course_code'];
            $courseIds = explode(',', $duplicate['course_ids']);
            $programIds = explode(',', $duplicate['program_ids']);
            
            // Get the first course entry as the primary one
            $primaryId = $courseIds[0];
            
            echo "\nProcessing course: $courseCode\n";
            echo "- Primary entry ID: $primaryId\n";
            echo "- Additional entries: " . implode(', ', array_slice($courseIds, 1)) . "\n";
            
            // Insert all program associations into course_programs table
            foreach ($programIds as $programId) {
                if (!empty($programId)) {
                    try {
                        $insertSQL = "INSERT IGNORE INTO course_programs (course_code, program_id) VALUES (?, ?)";
                        $stmt = $pdo->prepare($insertSQL);
                        $stmt->execute([$courseCode, $programId]);
                        
                        if ($stmt->rowCount() > 0) {
                            echo "  ✅ Added program association: Program ID $programId\n";
                        } else {
                            echo "  ⚠️  Program association already exists: Program ID $programId\n";
                        }
                    } catch (Exception $e) {
                        echo "  ❌ Error adding program association: " . $e->getMessage() . "\n";
                    }
                }
            }
            
            // Delete the duplicate course entries (keep only the primary one)
            $deleteIds = array_slice($courseIds, 1);
            if (!empty($deleteIds)) {
                $placeholders = str_repeat('?,', count($deleteIds) - 1) . '?';
                $deleteSQL = "DELETE FROM courses WHERE id IN ($placeholders)";
                $stmt = $pdo->prepare($deleteSQL);
                $stmt->execute($deleteIds);
                
                echo "  ✅ Deleted " . $stmt->rowCount() . " duplicate entries\n";
            }
            
            // Remove program_id from the primary entry since we're using course_programs now
            $updateSQL = "UPDATE courses SET program_id = NULL WHERE id = ?";
            $stmt = $pdo->prepare($updateSQL);
            $stmt->execute([$primaryId]);
            echo "  ✅ Cleared program_id from primary entry\n";
        }
    }
    
    // Step 4: Handle single course entries (migrate to course_programs)
    echo "\n=== STEP 4: MIGRATING SINGLE COURSE ENTRIES ===\n";
    $singleCoursesQuery = "
        SELECT course_code, program_id 
        FROM courses 
        WHERE program_id IS NOT NULL
    ";
    
    $stmt = $pdo->query($singleCoursesQuery);
    $singleCourses = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    $migrated = 0;
    foreach ($singleCourses as $course) {
        try {
            $insertSQL = "INSERT IGNORE INTO course_programs (course_code, program_id) VALUES (?, ?)";
            $stmt = $pdo->prepare($insertSQL);
            $stmt->execute([$course['course_code'], $course['program_id']]);
            
            if ($stmt->rowCount() > 0) {
                $migrated++;
                echo "✅ Migrated: {$course['course_code']} -> Program ID {$course['program_id']}\n";
            }
            
            // Clear program_id from courses table
            $updateSQL = "UPDATE courses SET program_id = NULL WHERE course_code = ? AND program_id = ?";
            $stmt = $pdo->prepare($updateSQL);
            $stmt->execute([$course['course_code'], $course['program_id']]);
            
        } catch (Exception $e) {
            echo "❌ Error migrating {$course['course_code']}: " . $e->getMessage() . "\n";
        }
    }
    
    echo "\n✅ Migrated $migrated single course entries\n";
    
    // Step 5: Show final results
    echo "\n=== STEP 5: FINAL RESULTS ===\n";
    
    // Show courses with their program associations
    $finalQuery = "
        SELECT 
            c.course_code,
            c.course_title,
            COUNT(cp.program_id) as program_count,
            GROUP_CONCAT(p.program_code ORDER BY p.program_code SEPARATOR ', ') as programs
        FROM courses c
        LEFT JOIN course_programs cp ON c.course_code = cp.course_code
        LEFT JOIN programs p ON cp.program_id = p.id
        GROUP BY c.course_code, c.course_title
        ORDER BY c.course_code
    ";
    
    $stmt = $pdo->query($finalQuery);
    $finalResults = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo "Courses with program associations:\n";
    foreach ($finalResults as $result) {
        if ($result['program_count'] > 0) {
            echo "- {$result['course_code']}: {$result['course_title']} -> {$result['programs']} ({$result['program_count']} programs)\n";
        } else {
            echo "- {$result['course_code']}: {$result['course_title']} -> No programs assigned\n";
        }
    }
    
    echo "\n✅ Course consolidation completed successfully!\n";
    echo "\nNext steps:\n";
    echo "1. Update the all-courses.php query to use course_programs table\n";
    echo "2. Update the course update API to handle multiple programs\n";
    echo "3. Test the system to ensure everything works correctly\n";
    
} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
    echo "Stack trace: " . $e->getTraceAsString() . "\n";
}
?>
