<?php
// Test script specifically for BLIS302
require_once 'includes/db_connection.php';

echo "<h2>BLIS302 Database Test</h2>";

try {
    // Check if BLIS302 course exists
    $courseQuery = "SELECT * FROM courses WHERE course_code = 'BLIS302'";
    $courseResult = $pdo->query($courseQuery);
    $course = $courseResult->fetch(PDO::FETCH_ASSOC);
    
    if ($course) {
        echo "<p>✅ BLIS302 course found:</p>";
        echo "<pre>" . print_r($course, true) . "</pre>";
    } else {
        echo "<p>❌ BLIS302 course not found</p>";
        
        // Create the course
        $insertCourse = "INSERT INTO courses (course_code, course_title, units, term, academic_year, year_level) VALUES 
            ('BLIS302', 'Organization of Information Sources', 3, '1st Semester', 'A.Y. 2020 - 2021', '1st Year')";
        $pdo->exec($insertCourse);
        echo "<p>✅ Created BLIS302 course</p>";
    }
    
    // Check programs table
    $programsQuery = "SELECT * FROM programs";
    $programs = $pdo->query($programsQuery)->fetchAll(PDO::FETCH_ASSOC);
    echo "<p>Programs in database:</p>";
    echo "<pre>" . print_r($programs, true) . "</pre>";
    
    // Check course_programs relationships
    $relationshipsQuery = "SELECT cp.course_code, p.program_code, p.color_code 
                          FROM course_programs cp 
                          JOIN programs p ON cp.program_id = p.id 
                          WHERE cp.course_code = 'BLIS302'";
    $relationships = $pdo->query($relationshipsQuery)->fetchAll(PDO::FETCH_ASSOC);
    
    if (!empty($relationships)) {
        echo "<p>✅ BLIS302 program relationships found:</p>";
        echo "<pre>" . print_r($relationships, true) . "</pre>";
    } else {
        echo "<p>❌ No program relationships found for BLIS302</p>";
        
        // Create relationship with BLIS program
        $blisProgram = $pdo->query("SELECT id FROM programs WHERE program_code = 'BLIS'")->fetch(PDO::FETCH_ASSOC);
        if ($blisProgram) {
            $linkQuery = "INSERT INTO course_programs (course_code, program_id) VALUES ('BLIS302', ?)";
            $linkStmt = $pdo->prepare($linkQuery);
            $linkStmt->execute([$blisProgram['id']]);
            echo "<p>✅ Linked BLIS302 to BLIS program</p>";
        } else {
            echo "<p>❌ BLIS program not found</p>";
        }
    }
    
    // Test the exact query used in course-details.php
    echo "<h3>Testing the exact query from course-details.php:</h3>";
    
    $testQuery = "
        SELECT DISTINCT p.program_code, p.color_code
        FROM courses c
        LEFT JOIN course_programs cp ON c.course_code = cp.course_code
        LEFT JOIN programs p ON cp.program_id = p.id
        WHERE c.course_code = 'BLIS302'
    ";
    
    $stmt = $pdo->prepare($testQuery);
    $stmt->execute();
    $programs = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo "<p>Query result: " . json_encode($programs) . "</p>";
    
    if (empty($programs) || (count($programs) === 1 && empty($programs[0]['program_code']))) {
        echo "<p>❌ Query returned no programs - this is why N/A is showing</p>";
    } else {
        echo "<p>✅ Query returned programs - should show program badges</p>";
    }
    
} catch (Exception $e) {
    echo "<p style='color: red;'>Error: " . htmlspecialchars($e->getMessage()) . "</p>";
}
?>
