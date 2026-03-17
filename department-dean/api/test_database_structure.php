<?php
// Test database structure for course updates
error_reporting(E_ALL);
ini_set('display_errors', 1);

try {
    require_once '../includes/db_connection.php';
    echo "✅ Database connection successful\n\n";
    
    // Check if courses table exists and its structure
    echo "=== COURSES TABLE ===\n";
    $stmt = $pdo->query("DESCRIBE courses");
    $columns = $stmt->fetchAll(PDO::FETCH_ASSOC);
    foreach ($columns as $column) {
        echo "- {$column['Field']}: {$column['Type']}\n";
    }
    
    // Check current course data
    echo "\n=== CURRENT COURSE DATA (BLIS302) ===\n";
    $stmt = $pdo->prepare("SELECT * FROM courses WHERE course_code = ?");
    $stmt->execute(['BLIS302']);
    $course = $stmt->fetch(PDO::FETCH_ASSOC);
    if ($course) {
        foreach ($course as $field => $value) {
            echo "- $field: $value\n";
        }
    } else {
        echo "Course BLIS302 not found\n";
    }
    
    // Check if course_programs table exists
    echo "\n=== COURSE_PROGRAMS TABLE ===\n";
    $stmt = $pdo->query("SHOW TABLES LIKE 'course_programs'");
    if ($stmt->rowCount() > 0) {
        echo "✅ course_programs table exists\n";
        
        // Check its structure
        $stmt = $pdo->query("DESCRIBE course_programs");
        $columns = $stmt->fetchAll(PDO::FETCH_ASSOC);
        foreach ($columns as $column) {
            echo "- {$column['Field']}: {$column['Type']}\n";
        }
        
        // Check current program associations for BLIS302
        echo "\n=== CURRENT PROGRAM ASSOCIATIONS (BLIS302) ===\n";
        $stmt = $pdo->prepare("SELECT * FROM course_programs WHERE course_code = ?");
        $stmt->execute(['BLIS302']);
        $associations = $stmt->fetchAll(PDO::FETCH_ASSOC);
        if ($associations) {
            foreach ($associations as $assoc) {
                echo "- Course: {$assoc['course_code']}, Program ID: {$assoc['program_id']}\n";
            }
        } else {
            echo "No program associations found for BLIS302\n";
        }
    } else {
        echo "❌ course_programs table does not exist\n";
    }
    
    // Check programs table
    echo "\n=== PROGRAMS TABLE ===\n";
    $stmt = $pdo->query("SELECT id, program_code, program_name FROM programs LIMIT 5");
    $programs = $stmt->fetchAll(PDO::FETCH_ASSOC);
    foreach ($programs as $program) {
        echo "- ID: {$program['id']}, Code: {$program['program_code']}, Name: {$program['program_name']}\n";
    }
    
} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
}
?>
