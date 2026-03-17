<?php
// Test script for multiple programs
require_once 'includes/db_connection.php';

echo "<h2>Multiple Programs Test</h2>";

try {
    // Check if course_programs table exists
    $tables = $pdo->query("SHOW TABLES LIKE 'course_programs'")->fetchAll(PDO::FETCH_COLUMN);
    
    if (empty($tables)) {
        echo "<p>❌ course_programs table doesn't exist</p>";
        
        // Create the table
        $pdo->exec("CREATE TABLE course_programs (
            id INT PRIMARY KEY AUTO_INCREMENT,
            course_code VARCHAR(20) NOT NULL,
            program_id INT NOT NULL,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            UNIQUE KEY unique_course_program (course_code, program_id)
        )");
        echo "<p>✅ Created course_programs table</p>";
    } else {
        echo "<p>✅ course_programs table exists</p>";
    }
    
    // Check if programs table has data
    $programs = $pdo->query("SELECT * FROM programs")->fetchAll(PDO::FETCH_ASSOC);
    echo "<p>Programs in database: " . count($programs) . "</p>";
    echo "<pre>" . print_r($programs, true) . "</pre>";
    
    // Check if BLIS302 has multiple programs
    $coursePrograms = $pdo->query("SELECT * FROM course_programs WHERE course_code = 'BLIS302'")->fetchAll(PDO::FETCH_ASSOC);
    echo "<p>BLIS302 programs: " . count($coursePrograms) . "</p>";
    echo "<pre>" . print_r($coursePrograms, true) . "</pre>";
    
    // If no programs for BLIS302, create some test data
    if (empty($coursePrograms)) {
        echo "<p>Creating test data for BLIS302...</p>";
        
        // Get program IDs
        $programIds = $pdo->query("SELECT id FROM programs")->fetchAll(PDO::FETCH_COLUMN);
        
        if (!empty($programIds)) {
            // Link BLIS302 to multiple programs
            foreach ($programIds as $programId) {
                try {
                    $pdo->prepare("INSERT INTO course_programs (course_code, program_id) VALUES ('BLIS302', ?)")
                        ->execute([$programId]);
                    echo "<p>✅ Linked BLIS302 to program ID: $programId</p>";
                } catch (Exception $e) {
                    echo "<p>⚠️ Could not link to program ID $programId: " . $e->getMessage() . "</p>";
                }
            }
        }
    }
    
    // Test the query used in course-details.php
    echo "<h3>Testing the exact query from course-details.php:</h3>";
    
    $testQuery = "
        SELECT DISTINCT p.program_code, p.color_code
        FROM course_programs cp
        JOIN programs p ON cp.program_id = p.id
        WHERE cp.course_code = 'BLIS302'
    ";
    
    $stmt = $pdo->prepare($testQuery);
    $stmt->execute();
    $result = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo "<p>Query result: " . json_encode($result) . "</p>";
    echo "<p>Number of programs: " . count($result) . "</p>";
    
    if (count($result) > 1) {
        echo "<p>✅ Multiple programs found - should show multiple badges</p>";
    } else if (count($result) === 1) {
        echo "<p>⚠️ Only one program found - might be correct</p>";
    } else {
        echo "<p>❌ No programs found - this is why N/A shows</p>";
    }
    
} catch (Exception $e) {
    echo "<p style='color: red;'>Error: " . htmlspecialchars($e->getMessage()) . "</p>";
}
?>
