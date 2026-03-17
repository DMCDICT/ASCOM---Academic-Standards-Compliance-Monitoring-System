<?php
// Check what's actually in the database
require_once '../config/db_connection.php';

echo "<h2>Database Structure Check</h2>";

try {
    // Check if course_programs table exists
    echo "<h3>1. Check if course_programs table exists:</h3>";
    $query = "SHOW TABLES LIKE 'course_programs'";
    $stmt = $pdo->prepare($query);
    $stmt->execute();
    $tableExists = $stmt->fetch();
    
    if ($tableExists) {
        echo "<p style='color: green;'>✅ course_programs table exists</p>";
        
        // Show table structure
        $query = "DESCRIBE course_programs";
        $stmt = $pdo->prepare($query);
        $stmt->execute();
        $structure = $stmt->fetchAll(PDO::FETCH_ASSOC);
        echo "<pre>";
        print_r($structure);
        echo "</pre>";
        
        // Show all data in course_programs
        $query = "SELECT * FROM course_programs";
        $stmt = $pdo->prepare($query);
        $stmt->execute();
        $data = $stmt->fetchAll(PDO::FETCH_ASSOC);
        echo "<h4>All course_programs data:</h4>";
        echo "<pre>";
        print_r($data);
        echo "</pre>";
        
    } else {
        echo "<p style='color: red;'>❌ course_programs table does NOT exist</p>";
    }
    
    // Check courses table
    echo "<h3>2. Courses table:</h3>";
    $query = "SELECT course_code, program_id FROM courses LIMIT 10";
    $stmt = $pdo->prepare($query);
    $stmt->execute();
    $courses = $stmt->fetchAll(PDO::FETCH_ASSOC);
    echo "<pre>";
    print_r($courses);
    echo "</pre>";
    
    // Check programs table
    echo "<h3>3. Programs table:</h3>";
    $query = "SELECT * FROM programs";
    $stmt = $pdo->prepare($query);
    $stmt->execute();
    $programs = $stmt->fetchAll(PDO::FETCH_ASSOC);
    echo "<pre>";
    print_r($programs);
    echo "</pre>";
    
} catch (Exception $e) {
    echo "<p style='color: red;'>Error: " . htmlspecialchars($e->getMessage()) . "</p>";
}
?>
