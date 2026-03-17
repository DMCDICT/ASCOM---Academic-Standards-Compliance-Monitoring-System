<?php
// Simple diagnostic script
require_once 'includes/db_connection.php';

echo "<h2>Database Diagnostic</h2>";

try {
    // Check if database connection works
    echo "<p>✅ Database connection successful</p>";
    
    // Check what tables exist
    $tables = $pdo->query("SHOW TABLES")->fetchAll(PDO::FETCH_COLUMN);
    echo "<p>Tables in database: " . implode(', ', $tables) . "</p>";
    
    // Check if courses table has data
    if (in_array('courses', $tables)) {
        $courses = $pdo->query("SELECT course_code, course_title FROM courses LIMIT 5")->fetchAll(PDO::FETCH_ASSOC);
        echo "<p>Courses in database:</p>";
        echo "<pre>" . print_r($courses, true) . "</pre>";
    }
    
    // Check if programs table has data
    if (in_array('programs', $tables)) {
        $programs = $pdo->query("SELECT * FROM programs")->fetchAll(PDO::FETCH_ASSOC);
        echo "<p>Programs in database:</p>";
        echo "<pre>" . print_r($programs, true) . "</pre>";
    }
    
    // Check if course_programs table has data
    if (in_array('course_programs', $tables)) {
        $relationships = $pdo->query("SELECT * FROM course_programs")->fetchAll(PDO::FETCH_ASSOC);
        echo "<p>Course-Program relationships:</p>";
        echo "<pre>" . print_r($relationships, true) . "</pre>";
    }
    
    // Test the exact query from course-details.php
    echo "<h3>Testing BLIS302 Query:</h3>";
    
    $testQuery = "
        SELECT DISTINCT p.program_code, p.color_code
        FROM courses c
        LEFT JOIN course_programs cp ON c.course_code = cp.course_code
        LEFT JOIN programs p ON cp.program_id = p.id
        WHERE c.course_code = 'BLIS302'
    ";
    
    try {
        $stmt = $pdo->prepare($testQuery);
        $stmt->execute();
        $result = $stmt->fetchAll(PDO::FETCH_ASSOC);
        echo "<p>Query result: " . json_encode($result) . "</p>";
        
        if (empty($result) || (count($result) === 1 && empty($result[0]['program_code']))) {
            echo "<p>❌ No programs found - this is why N/A shows</p>";
        } else {
            echo "<p>✅ Programs found - should show badges</p>";
        }
    } catch (Exception $e) {
        echo "<p>❌ Query failed: " . $e->getMessage() . "</p>";
    }
    
} catch (Exception $e) {
    echo "<p style='color: red;'>Database error: " . htmlspecialchars($e->getMessage()) . "</p>";
}
?>
