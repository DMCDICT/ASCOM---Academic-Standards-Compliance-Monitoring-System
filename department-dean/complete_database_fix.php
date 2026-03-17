<?php
// Complete database fix script
require_once 'includes/db_connection.php';

echo "<h2>Complete Database Fix</h2>";

try {
    // Step 1: Create all required tables
    echo "<h3>Step 1: Creating Tables</h3>";
    
    // Create courses table
    $create_courses_sql = "CREATE TABLE IF NOT EXISTS courses (
        id INT PRIMARY KEY AUTO_INCREMENT,
        course_code VARCHAR(20) UNIQUE NOT NULL,
        course_title VARCHAR(255) NOT NULL,
        units INT NOT NULL,
        term VARCHAR(100) NOT NULL,
        academic_year VARCHAR(100) NOT NULL,
        year_level VARCHAR(50) NOT NULL,
        status ENUM('Active', 'Inactive') DEFAULT 'Active',
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
    )";
    
    $pdo->exec($create_courses_sql);
    echo "<p>✅ Courses table ready</p>";
    
    // Create programs table
    $create_programs_sql = "CREATE TABLE IF NOT EXISTS programs (
        id INT PRIMARY KEY AUTO_INCREMENT,
        program_code VARCHAR(20) UNIQUE NOT NULL,
        program_name VARCHAR(255) NOT NULL,
        color_code VARCHAR(7) DEFAULT '#1976d2',
        status ENUM('Active', 'Inactive') DEFAULT 'Active',
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
    )";
    
    $pdo->exec($create_programs_sql);
    echo "<p>✅ Programs table ready</p>";
    
    // Create course_programs table
    $create_course_programs_sql = "CREATE TABLE IF NOT EXISTS course_programs (
        id INT PRIMARY KEY AUTO_INCREMENT,
        course_code VARCHAR(20) NOT NULL,
        program_id INT NOT NULL,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        UNIQUE KEY unique_course_program (course_code, program_id)
    )";
    
    $pdo->exec($create_course_programs_sql);
    echo "<p>✅ Course_programs table ready</p>";
    
    // Step 2: Insert sample data
    echo "<h3>Step 2: Inserting Sample Data</h3>";
    
    // Insert programs
    $programCount = $pdo->query("SELECT COUNT(*) FROM programs")->fetchColumn();
    if ($programCount == 0) {
        $pdo->exec("INSERT INTO programs (program_code, program_name, color_code) VALUES 
            ('BSCS', 'Bachelor of Science in Computer Science', '#1976d2'),
            ('BSIT', 'Bachelor of Science in Information Technology', '#4CAF50'),
            ('BLIS', 'Bachelor of Library and Information Science', '#FF9800'),
            ('BSCE', 'Bachelor of Science in Civil Engineering', '#9C27B0')");
        echo "<p>✅ Sample programs inserted</p>";
    } else {
        echo "<p>✅ Programs already exist ($programCount programs)</p>";
    }
    
    // Insert BLIS302 course if it doesn't exist
    $courseCheck = $pdo->prepare("SELECT id FROM courses WHERE course_code = 'BLIS302'");
    $courseCheck->execute();
    $courseId = $courseCheck->fetchColumn();
    
    if (!$courseId) {
        $pdo->exec("INSERT INTO courses (course_code, course_title, units, term, academic_year, year_level) VALUES 
            ('BLIS302', 'Organization of Information Sources', 3, '1st Semester', 'A.Y. 2020 - 2021', '1st Year')");
        echo "<p>✅ BLIS302 course created</p>";
    } else {
        echo "<p>✅ BLIS302 course already exists</p>";
    }
    
    // Step 3: Create course-program relationships
    echo "<h3>Step 3: Creating Course-Program Relationships</h3>";
    
    // Check if BLIS302 has program relationships
    $relationshipCheck = $pdo->prepare("SELECT COUNT(*) FROM course_programs WHERE course_code = 'BLIS302'");
    $relationshipCheck->execute();
    $relationshipCount = $relationshipCheck->fetchColumn();
    
    if ($relationshipCount == 0) {
        // Get BLIS program ID
        $blisProgram = $pdo->query("SELECT id FROM programs WHERE program_code = 'BLIS'")->fetch(PDO::FETCH_ASSOC);
        if ($blisProgram) {
            $pdo->prepare("INSERT INTO course_programs (course_code, program_id) VALUES ('BLIS302', ?)")
                ->execute([$blisProgram['id']]);
            echo "<p>✅ BLIS302 linked to BLIS program</p>";
        } else {
            echo "<p>❌ BLIS program not found</p>";
        }
    } else {
        echo "<p>✅ BLIS302 already has program relationships ($relationshipCount relationships)</p>";
    }
    
    // Step 4: Test the queries
    echo "<h3>Step 4: Testing Queries</h3>";
    
    // Test the course details query
    echo "<h4>Testing Course Details Query:</h4>";
    $courseQuery = "
        SELECT 
            c.id, c.course_code, c.course_title, c.units, c.term, c.academic_year, c.year_level
        FROM courses c
        WHERE c.course_code = 'BLIS302'
    ";
    $courseResult = $pdo->query($courseQuery)->fetch(PDO::FETCH_ASSOC);
    echo "<p>Course data: " . json_encode($courseResult) . "</p>";
    
    // Test the program query
    echo "<h4>Testing Program Query:</h4>";
    $programQuery = "
        SELECT p.id, p.program_code, p.program_name, p.color_code
        FROM course_programs cp
        JOIN programs p ON cp.program_id = p.id
        WHERE cp.course_code = 'BLIS302'
    ";
    $programResult = $pdo->query($programQuery)->fetchAll(PDO::FETCH_ASSOC);
    echo "<p>Program data: " . json_encode($programResult) . "</p>";
    
    // Test the card display query
    echo "<h4>Testing Card Display Query:</h4>";
    $cardQuery = "
        SELECT DISTINCT p.program_code, p.color_code
        FROM courses c
        LEFT JOIN course_programs cp ON c.course_code = cp.course_code
        LEFT JOIN programs p ON cp.program_id = p.id
        WHERE c.course_code = 'BLIS302'
    ";
    $cardResult = $pdo->query($cardQuery)->fetchAll(PDO::FETCH_ASSOC);
    echo "<p>Card display data: " . json_encode($cardResult) . "</p>";
    
    if (!empty($cardResult) && !empty($cardResult[0]['program_code'])) {
        echo "<p>✅ Card should display program badges</p>";
    } else {
        echo "<p>❌ Card will still show N/A</p>";
    }
    
    // Step 5: Show all data
    echo "<h3>Step 5: Database Summary</h3>";
    
    $allCourses = $pdo->query("SELECT course_code, course_title FROM courses")->fetchAll(PDO::FETCH_ASSOC);
    echo "<p>All courses: " . json_encode($allCourses) . "</p>";
    
    $allPrograms = $pdo->query("SELECT id, program_code, program_name, color_code FROM programs")->fetchAll(PDO::FETCH_ASSOC);
    echo "<p>All programs: " . json_encode($allPrograms) . "</p>";
    
    $allRelationships = $pdo->query("SELECT course_code, program_id FROM course_programs")->fetchAll(PDO::FETCH_ASSOC);
    echo "<p>All relationships: " . json_encode($allRelationships) . "</p>";
    
    echo "<p><strong>✅ Database fix completed! Try refreshing the course details page now.</strong></p>";
    
} catch (Exception $e) {
    echo "<p style='color: red;'>Error: " . htmlspecialchars($e->getMessage()) . "</p>";
    echo "<p>Stack trace: " . htmlspecialchars($e->getTraceAsString()) . "</p>";
}
?>
