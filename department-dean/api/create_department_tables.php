<?php
// create_department_tables.php
// Create program_heads and course_assignments tables for Dean functionality

require_once '../includes/db_connection.php';

try {
    // Create program_heads table
    $create_program_heads_sql = "CREATE TABLE IF NOT EXISTS program_heads (
        id INT AUTO_INCREMENT PRIMARY KEY,
        program_id INT NOT NULL,
        teacher_id INT NOT NULL,
        assigned_by INT,
        assigned_at DATETIME DEFAULT CURRENT_TIMESTAMP,
        is_active BOOLEAN DEFAULT TRUE,
        
        UNIQUE KEY unique_program (program_id),
        UNIQUE KEY unique_teacher (teacher_id),
        
        FOREIGN KEY (program_id) REFERENCES programs(id) ON DELETE CASCADE,
        FOREIGN KEY (teacher_id) REFERENCES users(id) ON DELETE CASCADE,
        
        INDEX idx_is_active (is_active)
    )";
    
    $pdo->exec($create_program_heads_sql);
    echo "✅ program_heads table created or already exists\n";
    
    // Create course_assignments table
    $create_course_assignments_sql = "CREATE TABLE IF NOT EXISTS course_assignments (
        id INT AUTO_INCREMENT PRIMARY KEY,
        course_id INT NOT NULL,
        teacher_id INT NOT NULL,
        assigned_by INT,
        assigned_at DATETIME DEFAULT CURRENT_TIMESTAMP,
        is_active BOOLEAN DEFAULT TRUE,
        
        UNIQUE KEY unique_course_teacher (course_id, teacher_id),
        
        FOREIGN KEY (course_id) REFERENCES courses(id) ON DELETE CASCADE,
        FOREIGN KEY (teacher_id) REFERENCES users(id) ON DELETE CASCADE,
        
        INDEX idx_course (course_id),
        INDEX idx_teacher (teacher_id),
        INDEX idx_is_active (is_active)
    )";
    
    $pdo->exec($create_course_assignments_sql);
    echo "✅ course_assignments table created or already exists\n";
    
    // Check if courses table has department_id column
    $check_dept_column = $pdo->query("SHOW COLUMNS FROM courses LIKE 'department_id'");
    if ($check_dept_column->rowCount() === 0) {
        $pdo->exec("ALTER TABLE courses ADD COLUMN department_id INT AFTER program_id");
        echo "✅ Added department_id to courses table\n";
    }
    
    // Check if courses table has faculty_id column (for primary instructor)
    $check_faculty_column = $pdo->query("SHOW COLUMNS FROM courses LIKE 'faculty_id'");
    if ($check_faculty_column->rowCount() === 0) {
        $pdo->exec("ALTER TABLE courses ADD COLUMN faculty_id INT AFTER department_id");
        echo "✅ Added faculty_id to courses table\n";
    }
    
    echo "\n✅ All department management tables created successfully!\n";
    
} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
}
?>
