<?php
// Setup tables script
require_once 'includes/db_connection.php';

echo "Setting up tables...\n";

try {
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
        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        INDEX idx_course_code (course_code),
        INDEX idx_status (status)
    )";
    
    $pdo->exec($create_courses_sql);
    echo "✅ Courses table created or already exists\n";
    
    // Create course_programs table (junction table)
    $create_course_programs_sql = "CREATE TABLE IF NOT EXISTS course_programs (
        id INT PRIMARY KEY AUTO_INCREMENT,
        course_code VARCHAR(20) NOT NULL,
        program_id INT NOT NULL,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (course_code) REFERENCES courses(course_code) ON DELETE CASCADE,
        UNIQUE KEY unique_course_program (course_code, program_id)
    )";
    
    $pdo->exec($create_course_programs_sql);
    echo "✅ Course_programs table created or already exists\n";
    
    // Create programs table if it doesn't exist
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
    echo "✅ Programs table created or already exists\n";
    
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
        echo "✅ Sample programs inserted\n";
    }
    
    // Insert sample course if none exist
    $check_courses = $pdo->query("SELECT COUNT(*) FROM courses");
    $course_count = $check_courses->fetchColumn();
    
    if ($course_count == 0) {
        $insert_course_sql = "INSERT INTO courses (course_code, course_title, units, term, academic_year, year_level) VALUES 
            ('BLIS103', 'Foundations of Library & Info Science', 3, '1st Semester', 'A.Y. 2020 - 2021', '1st Year')";
        
        $pdo->exec($insert_course_sql);
        echo "✅ Sample course inserted\n";
        
        // Link course to program
        $link_course_program_sql = "INSERT INTO course_programs (course_code, program_id) VALUES 
            ('BLIS103', (SELECT id FROM programs WHERE program_code = 'BLIS' LIMIT 1))";
        
        $pdo->exec($link_course_program_sql);
        echo "✅ Course linked to program\n";
    }
    
    echo "\n✅ All tables created successfully!\n";
    
} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
}
?>
