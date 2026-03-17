<?php
// Check what's actually in the courses table
require_once '../config/db_connection.php';

echo "<h2>Course Data Check</h2>";

try {
    // Check what's in courses table
    echo "<h3>1. All courses with their programs:</h3>";
    $query = "
        SELECT 
            c.course_code,
            c.course_title,
            c.program_id,
            p.program_code,
            p.program_name,
            p.color_code
        FROM courses c
        LEFT JOIN programs p ON c.program_id = p.id
        ORDER BY c.course_code
    ";
    $stmt = $pdo->prepare($query);
    $stmt->execute();
    $courses = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo "<table border='1' style='border-collapse: collapse; width: 100%;'>";
    echo "<tr><th>Course Code</th><th>Course Title</th><th>Program ID</th><th>Program Code</th><th>Program Name</th><th>Color</th></tr>";
    
    foreach ($courses as $course) {
        echo "<tr>";
        echo "<td>" . htmlspecialchars($course['course_code']) . "</td>";
        echo "<td>" . htmlspecialchars($course['course_title']) . "</td>";
        echo "<td>" . htmlspecialchars($course['program_id'] ?? 'NULL') . "</td>";
        echo "<td>" . htmlspecialchars($course['program_code'] ?? 'NULL') . "</td>";
        echo "<td>" . htmlspecialchars($course['program_name'] ?? 'NULL') . "</td>";
        echo "<td>" . htmlspecialchars($course['color_code'] ?? 'NULL') . "</td>";
        echo "</tr>";
    }
    echo "</table>";
    
    // Check if there are duplicate course codes (which would mean multiple programs)
    echo "<h3>2. Check for duplicate course codes (multiple programs):</h3>";
    $query = "
        SELECT course_code, COUNT(*) as count
        FROM courses 
        GROUP BY course_code 
        HAVING COUNT(*) > 1
        ORDER BY course_code
    ";
    $stmt = $pdo->prepare($query);
    $stmt->execute();
    $duplicates = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    if ($duplicates) {
        echo "<p style='color: green;'>Found courses with multiple entries:</p>";
        echo "<pre>";
        print_r($duplicates);
        echo "</pre>";
        
        // Show details for each duplicate
        foreach ($duplicates as $dup) {
            echo "<h4>Details for " . $dup['course_code'] . ":</h4>";
            $query = "SELECT * FROM courses WHERE course_code = ?";
            $stmt = $pdo->prepare($query);
            $stmt->execute([$dup['course_code']]);
            $details = $stmt->fetchAll(PDO::FETCH_ASSOC);
            echo "<pre>";
            print_r($details);
            echo "</pre>";
        }
    } else {
        echo "<p style='color: orange;'>No duplicate course codes found - each course has only one program</p>";
    }
    
} catch (Exception $e) {
    echo "<p style='color: red;'>Error: " . htmlspecialchars($e->getMessage()) . "</p>";
}
?>
