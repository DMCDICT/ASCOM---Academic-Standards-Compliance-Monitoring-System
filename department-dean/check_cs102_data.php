<?php
// Check what's in the database for CS102
require_once '../config/db_connection.php';

echo "<h2>CS102 Database Check</h2>";

try {
    // Check courses table for CS102
    echo "<h3>1. Courses Table for CS102:</h3>";
    $query = "SELECT * FROM courses WHERE course_code = 'CS102'";
    $stmt = $pdo->prepare($query);
    $stmt->execute();
    $course = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if ($course) {
        echo "<pre>";
        print_r($course);
        echo "</pre>";
    } else {
        echo "<p style='color: red;'>CS102 not found in courses table!</p>";
    }
    
    // Check what the course details query would return
    echo "<h3>2. Course Details Query (same as course-details.php):</h3>";
    $courseQuery = "
        SELECT 
            c.id,
            c.course_code,
            c.course_title,
            c.units,
            c.program_id,
            c.faculty_id,
            c.status,
            c.term,
            c.academic_year,
            c.year_level,
            c.created_by,
            c.created_at,
            c.updated_at,
            CONCAT(u.first_name, ' ', u.last_name) as faculty_name,
            p.program_code,
            p.program_name,
            p.color_code
        FROM courses c
        LEFT JOIN users u ON c.faculty_id = u.id
        LEFT JOIN programs p ON c.program_id = p.id
        WHERE c.course_code = ?
    ";
    $courseStmt = $pdo->prepare($courseQuery);
    $courseStmt->execute(['CS102']);
    $courseDetails = $courseStmt->fetch(PDO::FETCH_ASSOC);
    
    if ($courseDetails) {
        echo "<pre>";
        print_r($courseDetails);
        echo "</pre>";
        
        // Show what the program display logic would use
        echo "<h3>3. Program Display Logic:</h3>";
        if (!empty($courseDetails['program_code'])) {
            echo "<p style='color: green;'>Program Code: " . htmlspecialchars($courseDetails['program_code']) . "</p>";
            echo "<p style='color: green;'>Program Name: " . htmlspecialchars($courseDetails['program_name']) . "</p>";
            echo "<p style='color: green;'>Color Code: " . htmlspecialchars($courseDetails['color_code']) . "</p>";
        } else {
            echo "<p style='color: red;'>No program_code found!</p>";
        }
    } else {
        echo "<p style='color: red;'>CS102 not found with course details query!</p>";
    }
    
    // Check programs table
    echo "<h3>4. Programs Table:</h3>";
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
    
} catch (Exception $e) {
    echo "<p style='color: red;'>Error: " . htmlspecialchars($e->getMessage()) . "</p>";
}
?>
