<?php
// Add multiple programs to courses by creating duplicate course entries
require_once '../config/db_connection.php';

echo "<h2>Adding Multiple Programs to Courses</h2>";

try {
    // Get program IDs
    $programs = [];
    $query = "SELECT id, program_code FROM programs";
    $stmt = $pdo->prepare($query);
    $stmt->execute();
    $programData = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    foreach ($programData as $program) {
        $programs[$program['program_code']] = $program['id'];
    }
    
    echo "<h3>Available programs:</h3>";
    echo "<pre>";
    print_r($programs);
    echo "</pre>";
    
    // Add multiple programs for CS102 by creating duplicate entries
    echo "<h3>Adding multiple programs for CS102:</h3>";
    
    // First, get the original CS102 data
    $query = "SELECT * FROM courses WHERE course_code = 'CS102' LIMIT 1";
    $stmt = $pdo->prepare($query);
    $stmt->execute();
    $originalCourse = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if ($originalCourse) {
        echo "<p>Original CS102 data:</p>";
        echo "<pre>";
        print_r($originalCourse);
        echo "</pre>";
        
        // Add CS102 with BSCS program
        if (isset($programs['BSCS'])) {
            $query = "INSERT INTO courses (course_code, course_title, units, program_id, faculty_id, status, term, academic_year, year_level, created_by, created_at, updated_at) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, NOW(), NOW())";
            $stmt = $pdo->prepare($query);
            $stmt->execute([
                'CS102',
                $originalCourse['course_title'],
                $originalCourse['units'],
                $programs['BSCS'],
                $originalCourse['faculty_id'],
                $originalCourse['status'],
                $originalCourse['term'],
                $originalCourse['academic_year'],
                $originalCourse['year_level'],
                $originalCourse['created_by']
            ]);
            echo "<p style='color: green;'>✅ Added CS102 with BSCS program</p>";
        }
        
        // Add CS102 with BSIT program
        if (isset($programs['BSIT'])) {
            $query = "INSERT INTO courses (course_code, course_title, units, program_id, faculty_id, status, term, academic_year, year_level, created_by, created_at, updated_at) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, NOW(), NOW())";
            $stmt = $pdo->prepare($query);
            $stmt->execute([
                'CS102',
                $originalCourse['course_title'],
                $originalCourse['units'],
                $programs['BSIT'],
                $originalCourse['faculty_id'],
                $originalCourse['status'],
                $originalCourse['term'],
                $originalCourse['academic_year'],
                $originalCourse['year_level'],
                $originalCourse['created_by']
            ]);
            echo "<p style='color: green;'>✅ Added CS102 with BSIT program</p>";
        }
        
        // Add CS102 with BLIS program
        if (isset($programs['BLIS'])) {
            $query = "INSERT INTO courses (course_code, course_title, units, program_id, faculty_id, status, term, academic_year, year_level, created_by, created_at, updated_at) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, NOW(), NOW())";
            $stmt = $pdo->prepare($query);
            $stmt->execute([
                'CS102',
                $originalCourse['course_title'],
                $originalCourse['units'],
                $programs['BLIS'],
                $originalCourse['faculty_id'],
                $originalCourse['status'],
                $originalCourse['term'],
                $originalCourse['academic_year'],
                $originalCourse['year_level'],
                $originalCourse['created_by']
            ]);
            echo "<p style='color: green;'>✅ Added CS102 with BLIS program</p>";
        }
    } else {
        echo "<p style='color: red;'>❌ CS102 not found in database</p>";
    }
    
    // Verify the data
    echo "<h3>Verification - All CS102 entries:</h3>";
    $query = "
        SELECT c.course_code, c.course_title, p.program_code, p.color_code
        FROM courses c
        LEFT JOIN programs p ON c.program_id = p.id
        WHERE c.course_code = 'CS102'
        ORDER BY p.program_code
    ";
    $stmt = $pdo->prepare($query);
    $stmt->execute();
    $results = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo "<pre>";
    print_r($results);
    echo "</pre>";
    
    echo "<p style='color: green; font-weight: bold;'>✅ Multiple programs added! Now CS102 should show multiple program badges.</p>";
    
} catch (Exception $e) {
    echo "<p style='color: red;'>Error: " . htmlspecialchars($e->getMessage()) . "</p>";
}
?>
