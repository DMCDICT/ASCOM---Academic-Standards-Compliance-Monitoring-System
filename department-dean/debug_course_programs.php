<?php
// Debug script to check course_programs table structure and data
require_once 'includes/db_connection.php';

echo "<h2>Course Programs Table Debug</h2>";

try {
    // Check if course_programs table exists
    $tableCheck = $pdo->query("SHOW TABLES LIKE 'course_programs'");
    $tableExists = $tableCheck->fetch();
    
    if ($tableExists) {
        echo "<h3>✅ course_programs table exists</h3>";
        
        // Show table structure
        echo "<h4>Table Structure:</h4>";
        $structure = $pdo->query("DESCRIBE course_programs");
        echo "<table border='1'><tr><th>Field</th><th>Type</th><th>Null</th><th>Key</th><th>Default</th><th>Extra</th></tr>";
        while ($row = $structure->fetch(PDO::FETCH_ASSOC)) {
            echo "<tr>";
            foreach ($row as $value) {
                echo "<td>" . htmlspecialchars($value) . "</td>";
            }
            echo "</tr>";
        }
        echo "</table>";
        
        // Show sample data
        echo "<h4>Sample Data:</h4>";
        $data = $pdo->query("SELECT * FROM course_programs LIMIT 10");
        $rows = $data->fetchAll(PDO::FETCH_ASSOC);
        
        if (!empty($rows)) {
            echo "<table border='1'>";
            echo "<tr>";
            foreach (array_keys($rows[0]) as $header) {
                echo "<th>" . htmlspecialchars($header) . "</th>";
            }
            echo "</tr>";
            
            foreach ($rows as $row) {
                echo "<tr>";
                foreach ($row as $value) {
                    echo "<td>" . htmlspecialchars($value) . "</td>";
                }
                echo "</tr>";
            }
            echo "</table>";
        } else {
            echo "<p>No data found in course_programs table</p>";
        }
        
        // Check for specific course
        $courseCode = $_GET['course_code'] ?? 'BLIS103';
        echo "<h4>Programs for Course: $courseCode</h4>";
        
        $courseQuery = "
            SELECT c.id as course_id, c.course_code, c.course_title,
                   cp.program_id, p.program_code, p.program_name, p.color_code
            FROM courses c
            LEFT JOIN course_programs cp ON c.id = cp.course_id
            LEFT JOIN programs p ON cp.program_id = p.id
            WHERE c.course_code = ?
        ";
        
        $stmt = $pdo->prepare($courseQuery);
        $stmt->execute([$courseCode]);
        $coursePrograms = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        if (!empty($coursePrograms)) {
            echo "<table border='1'>";
            echo "<tr><th>Course ID</th><th>Course Code</th><th>Course Title</th><th>Program ID</th><th>Program Code</th><th>Program Name</th><th>Color Code</th></tr>";
            foreach ($coursePrograms as $row) {
                echo "<tr>";
                foreach ($row as $value) {
                    echo "<td>" . htmlspecialchars($value ?? 'NULL') . "</td>";
                }
                echo "</tr>";
            }
            echo "</table>";
        } else {
            echo "<p>No programs found for course $courseCode</p>";
        }
        
    } else {
        echo "<h3>❌ course_programs table does not exist</h3>";
        echo "<p>This explains why program updates are not being reflected!</p>";
    }
    
} catch (Exception $e) {
    echo "<p style='color: red;'>Error: " . htmlspecialchars($e->getMessage()) . "</p>";
}
?>
