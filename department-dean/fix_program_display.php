<?php
// Fix program display issue
require_once 'includes/db_connection.php';

echo "<h2>Fixing Program Display</h2>";

try {
    // Check if tables exist and create them if needed
    $tables = ['courses', 'programs', 'course_programs'];
    
    foreach ($tables as $table) {
        $check = $pdo->query("SHOW TABLES LIKE '$table'");
        if (!$check->fetch()) {
            echo "<p>Creating table: $table</p>";
            
            if ($table === 'courses') {
                $pdo->exec("CREATE TABLE courses (
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
                )");
            } elseif ($table === 'programs') {
                $pdo->exec("CREATE TABLE programs (
                    id INT PRIMARY KEY AUTO_INCREMENT,
                    program_code VARCHAR(20) UNIQUE NOT NULL,
                    program_name VARCHAR(255) NOT NULL,
                    color_code VARCHAR(7) DEFAULT '#1976d2',
                    status ENUM('Active', 'Inactive') DEFAULT 'Active',
                    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
                )");
            } elseif ($table === 'course_programs') {
                $pdo->exec("CREATE TABLE course_programs (
                    id INT PRIMARY KEY AUTO_INCREMENT,
                    course_code VARCHAR(20) NOT NULL,
                    program_id INT NOT NULL,
                    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                    UNIQUE KEY unique_course_program (course_code, program_id)
                )");
            }
        }
    }
    
    // Insert sample programs if none exist
    $programCount = $pdo->query("SELECT COUNT(*) FROM programs")->fetchColumn();
    if ($programCount == 0) {
        $pdo->exec("INSERT INTO programs (program_code, program_name, color_code) VALUES 
            ('BSCS', 'Bachelor of Science in Computer Science', '#1976d2'),
            ('BSIT', 'Bachelor of Science in Information Technology', '#4CAF50'),
            ('BLIS', 'Bachelor of Library and Information Science', '#FF9800'),
            ('BSCE', 'Bachelor of Science in Civil Engineering', '#9C27B0')");
        echo "<p>✅ Sample programs inserted</p>";
    }
    
    // Check if BLIS302 course exists
    $courseCheck = $pdo->prepare("SELECT id FROM courses WHERE course_code = 'BLIS302'");
    $courseCheck->execute();
    $courseId = $courseCheck->fetchColumn();
    
    if (!$courseId) {
        $pdo->exec("INSERT INTO courses (course_code, course_title, units, term, academic_year, year_level) VALUES 
            ('BLIS302', 'Organization of Information Sources', 3, '1st Semester', 'A.Y. 2020 - 2021', '1st Year')");
        echo "<p>✅ BLIS302 course created</p>";
    }
    
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
        }
    }
    
    // Test the query
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
    
    echo "<p>Query result for BLIS302: " . json_encode($programs) . "</p>";
    
    if (!empty($programs) && !empty($programs[0]['program_code'])) {
        echo "<p>✅ Program data found - N/A should be fixed!</p>";
    } else {
        echo "<p>❌ Still no program data found</p>";
    }
    
} catch (Exception $e) {
    echo "<p style='color: red;'>Error: " . htmlspecialchars($e->getMessage()) . "</p>";
}
?>
