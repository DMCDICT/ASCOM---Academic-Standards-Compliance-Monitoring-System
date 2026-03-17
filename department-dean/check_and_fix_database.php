<?php
// Check and fix database tables
require_once 'includes/db_connection.php';

echo "<h2>Database Check and Fix</h2>";

try {
    // Check if tables exist
    $tables = ['courses', 'programs', 'course_programs'];
    
    foreach ($tables as $table) {
        $check = $pdo->query("SHOW TABLES LIKE '$table'");
        if ($check->fetch()) {
            echo "<p>✅ Table '$table' exists</p>";
        } else {
            echo "<p>❌ Table '$table' does not exist - creating it...</p>";
        }
    }
    
    // Create courses table if it doesn't exist
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
        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        INDEX idx_course_code (course_code),
        INDEX idx_status (status)
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
    
    // Insert sample programs if none exist
    $check_programs = $pdo->query("SELECT COUNT(*) FROM programs");
    $program_count = $check_programs->fetchColumn();
    
    if ($program_count == 0) {
        $insert_programs_sql = "INSERT INTO programs (program_code, program_name, color_code) VALUES 
            ('BSCS', 'Bachelor of Science in Computer Science', '#1976d2'),
            ('BSIT', 'Bachelor of Science in Information Technology', '#4CAF50'),
            ('BLIS', 'Bachelor of Library and Information Science', '#FF9800'),
            ('BSCE', 'Bachelor of Science in Civil Engineering', '#9C27B0')";
        
        $pdo->exec($insert_programs_sql);
        echo "<p>✅ Sample programs inserted</p>";
    } else {
        echo "<p>✅ Programs already exist ($program_count programs)</p>";
    }
    
    // Check what courses exist
    $courses = $pdo->query("SELECT course_code, course_title FROM courses")->fetchAll(PDO::FETCH_ASSOC);
    echo "<p>Existing courses: " . json_encode($courses) . "</p>";
    
    // Check course_programs relationships
    $relationships = $pdo->query("SELECT cp.course_code, p.program_code, p.color_code 
                                 FROM course_programs cp 
                                 JOIN programs p ON cp.program_id = p.id")->fetchAll(PDO::FETCH_ASSOC);
    echo "<p>Course-Program relationships: " . json_encode($relationships) . "</p>";
    
    // Test the specific query for BLIS302
    echo "<h3>Testing query for BLIS302:</h3>";
    
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
    
    echo "<p>Programs found for BLIS302: " . json_encode($programs) . "</p>";
    
    if (empty($programs) || (count($programs) === 1 && empty($programs[0]['program_code']))) {
        echo "<p>No programs found for BLIS302. Let's create a relationship...</p>";
        
        // First, check if BLIS302 course exists
        $courseCheck = $pdo->prepare("SELECT id FROM courses WHERE course_code = 'BLIS302'");
        $courseCheck->execute();
        $courseId = $courseCheck->fetchColumn();
        
        if ($courseId) {
            echo "<p>✅ BLIS302 course exists (ID: $courseId)</p>";
            
            // Get BLIS program ID
            $programCheck = $pdo->prepare("SELECT id FROM programs WHERE program_code = 'BLIS'");
            $programCheck->execute();
            $programId = $programCheck->fetchColumn();
            
            if ($programId) {
                echo "<p>✅ BLIS program exists (ID: $programId)</p>";
                
                // Create relationship
                try {
                    $linkSql = "INSERT INTO course_programs (course_code, program_id) VALUES ('BLIS302', ?)";
                    $linkStmt = $pdo->prepare($linkSql);
                    $linkStmt->execute([$programId]);
                    echo "<p>✅ Linked BLIS302 to BLIS program</p>";
                } catch (Exception $e) {
                    echo "<p>⚠️ Relationship might already exist: " . $e->getMessage() . "</p>";
                }
            } else {
                echo "<p>❌ BLIS program not found</p>";
            }
        } else {
            echo "<p>❌ BLIS302 course not found in database</p>";
        }
    }
    
    // Test the query again
    $stmt = $pdo->prepare($testQuery);
    $stmt->execute();
    $programs = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo "<p>Final result for BLIS302: " . json_encode($programs) . "</p>";
    
} catch (Exception $e) {
    echo "<p style='color: red;'>Error: " . htmlspecialchars($e->getMessage()) . "</p>";
}
?>
