<?php
// Check what's actually in the database for BLIS302
require_once '../config/db_connection.php';

echo "<h2>BLIS302 Database Check</h2>";

try {
    // Check courses table
    echo "<h3>1. Courses Table:</h3>";
    $query = "SELECT * FROM courses WHERE course_code = 'BLIS302'";
    $stmt = $pdo->prepare($query);
    $stmt->execute();
    $course = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if ($course) {
        echo "<pre>";
        print_r($course);
        echo "</pre>";
    } else {
        echo "<p style='color: red;'>BLIS302 not found in courses table!</p>";
    }
    
    // Check programs table
    echo "<h3>2. Programs Table:</h3>";
    $query = "SELECT * FROM programs ORDER BY program_code";
    $stmt = $pdo->prepare($query);
    $stmt->execute();
    $programs = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    if ($programs) {
        echo "<pre>";
        print_r($programs);
        echo "</pre>";
    } else {
        echo "<p style='color: red;'>No programs found in programs table!</p>";
    }
    
    // Check course_programs table
    echo "<h3>3. Course_Programs Table:</h3>";
    $query = "SELECT * FROM course_programs WHERE course_code = 'BLIS302'";
    $stmt = $pdo->prepare($query);
    $stmt->execute();
    $coursePrograms = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    if ($coursePrograms) {
        echo "<pre>";
        print_r($coursePrograms);
        echo "</pre>";
    } else {
        echo "<p style='color: red;'>No course_programs found for BLIS302!</p>";
    }
    
    // Check if course_programs table exists
    echo "<h3>4. Table Structure Check:</h3>";
    $query = "SHOW TABLES LIKE 'course_programs'";
    $stmt = $pdo->prepare($query);
    $stmt->execute();
    $tableExists = $stmt->fetch();
    
    if ($tableExists) {
        echo "<p style='color: green;'>course_programs table exists</p>";
        
        // Show table structure
        $query = "DESCRIBE course_programs";
        $stmt = $pdo->prepare($query);
        $stmt->execute();
        $structure = $stmt->fetchAll(PDO::FETCH_ASSOC);
        echo "<pre>";
        print_r($structure);
        echo "</pre>";
    } else {
        echo "<p style='color: red;'>course_programs table does NOT exist!</p>";
    }
    
} catch (Exception $e) {
    echo "<p style='color: red;'>Error: " . htmlspecialchars($e->getMessage()) . "</p>";
}
?>
