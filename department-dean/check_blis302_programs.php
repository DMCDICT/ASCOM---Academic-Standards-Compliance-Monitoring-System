<?php
// Check what programs are associated with BLIS302
require_once '../config/db_connection.php';

echo "<h2>BLIS302 Program Associations Check</h2>";

try {
    // Check what's in course_programs table for BLIS302
    echo "<h3>1. Course_Programs Table for BLIS302:</h3>";
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
    
    // Check the query used in course-details.php
    echo "<h3>2. Query Used in Course Details Page:</h3>";
    $programsQuery = "
        SELECT DISTINCT p.program_code, p.color_code
        FROM course_programs cp
        JOIN programs p ON cp.program_id = p.id
        WHERE cp.course_code = ?
    ";
    $programsStmt = $pdo->prepare($programsQuery);
    $programsStmt->execute(['BLIS302']);
    $programs = $programsStmt->fetchAll(PDO::FETCH_ASSOC);
    
    if ($programs) {
        echo "<p style='color: green;'>Found " . count($programs) . " programs:</p>";
        echo "<pre>";
        print_r($programs);
        echo "</pre>";
    } else {
        echo "<p style='color: red;'>No programs found with the course-details.php query!</p>";
    }
    
    // Check if programs table has the data
    echo "<h3>3. Programs Table:</h3>";
    $query = "SELECT * FROM programs ORDER BY program_code";
    $stmt = $pdo->prepare($query);
    $stmt->execute();
    $allPrograms = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    if ($allPrograms) {
        echo "<p>All programs in database:</p>";
        echo "<pre>";
        print_r($allPrograms);
        echo "</pre>";
    } else {
        echo "<p style='color: red;'>No programs found in programs table!</p>";
    }
    
    // Check courses table for BLIS302
    echo "<h3>4. Courses Table for BLIS302:</h3>";
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
    
} catch (Exception $e) {
    echo "<p style='color: red;'>Error: " . htmlspecialchars($e->getMessage()) . "</p>";
}
?>
