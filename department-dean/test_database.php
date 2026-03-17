<?php
// Simple test to check database tables and data
require_once 'includes/db_connection.php';

echo "<h2>Database Test</h2>";

try {
    // Check if tables exist
    $tables = ['courses', 'programs', 'course_programs'];
    
    foreach ($tables as $table) {
        $check = $pdo->query("SHOW TABLES LIKE '$table'");
        if ($check->fetch()) {
            echo "<p>✅ Table '$table' exists</p>";
            
            // Show sample data
            $data = $pdo->query("SELECT * FROM $table LIMIT 3");
            $rows = $data->fetchAll(PDO::FETCH_ASSOC);
            
            if (!empty($rows)) {
                echo "<p>Sample data from $table:</p>";
                echo "<pre>" . print_r($rows, true) . "</pre>";
            } else {
                echo "<p>No data in $table</p>";
            }
        } else {
            echo "<p>❌ Table '$table' does not exist</p>";
        }
    }
    
    // Test the specific query for BLIS103
    echo "<h3>Testing program query for BLIS103:</h3>";
    
    $testQuery = "
        SELECT DISTINCT p.program_code, p.color_code
        FROM courses c
        LEFT JOIN course_programs cp ON c.course_code = cp.course_code
        LEFT JOIN programs p ON cp.program_id = p.id
        WHERE c.course_code = 'BLIS103'
    ";
    
    $stmt = $pdo->prepare($testQuery);
    $stmt->execute();
    $programs = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo "<p>Programs found: " . json_encode($programs) . "</p>";
    
    if (empty($programs) || (count($programs) === 1 && empty($programs[0]['program_code']))) {
        echo "<p>No programs found in course_programs table, trying fallback...</p>";
        
        $fallbackQuery = "
            SELECT DISTINCT p.program_code, p.color_code
            FROM courses c
            LEFT JOIN programs p ON c.program_id = p.id
            WHERE c.course_code = 'BLIS103'
        ";
        
        $stmt = $pdo->prepare($fallbackQuery);
        $stmt->execute();
        $programs = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        echo "<p>Fallback programs found: " . json_encode($programs) . "</p>";
    }
    
} catch (Exception $e) {
    echo "<p style='color: red;'>Error: " . htmlspecialchars($e->getMessage()) . "</p>";
}
?>
