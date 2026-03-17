<?php
// Script to create the courses table with proper structure
header('Content-Type: text/plain');

try {
    require_once 'department-dean/includes/db_connection.php';
    
    echo "=== Creating Courses Table ===\n\n";
    
    // Check if courses table already exists
    $checkQuery = "SHOW TABLES LIKE 'courses'";
    $stmt = $pdo->prepare($checkQuery);
    $stmt->execute();
    $tableExists = $stmt->rowCount() > 0;
    
    if ($tableExists) {
        echo "✅ Courses table already exists!\n";
        echo "Dropping existing table to recreate with proper structure...\n";
        $pdo->exec("DROP TABLE courses");
    }
    
    // Create the courses table with all required fields
    $createTableQuery = "
        CREATE TABLE courses (
            id INT AUTO_INCREMENT PRIMARY KEY,
            course_code VARCHAR(20) NOT NULL,
            course_title VARCHAR(200) NOT NULL,
            units INT DEFAULT 3,
            program_id INT,
            faculty_id INT NULL,
            status ENUM('Active', 'Inactive') DEFAULT 'Active',
            term VARCHAR(50) DEFAULT '1st Semester',
            academic_year VARCHAR(20) DEFAULT 'A.Y. 2025-2026',
            year_level VARCHAR(20) DEFAULT '1st Year',
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            FOREIGN KEY (program_id) REFERENCES programs(id) ON DELETE SET NULL,
            FOREIGN KEY (faculty_id) REFERENCES users(id) ON DELETE SET NULL,
            UNIQUE KEY unique_course_program (course_code, program_id)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
    ";
    
    $pdo->exec($createTableQuery);
    echo "✅ Courses table created successfully!\n\n";
    
    // Show the table structure
    echo "Courses table structure:\n";
    echo "========================\n";
    $describeQuery = "DESCRIBE courses";
    $stmt = $pdo->prepare($describeQuery);
    $stmt->execute();
    $columns = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    foreach ($columns as $column) {
        $null = $column['Null'] === 'YES' ? 'NULL' : 'NOT NULL';
        $default = $column['Default'] ? "DEFAULT '{$column['Default']}'" : '';
        $key = $column['Key'] ? "({$column['Key']})" : '';
        echo "- {$column['Field']} ({$column['Type']}) {$null} {$default} {$key}\n";
    }
    
    echo "\n=== Adding Sample Course Data ===\n";
    
    // Get some program IDs to use for sample data
    $programQuery = "SELECT id, program_code, program_name FROM programs LIMIT 5";
    $stmt = $pdo->prepare($programQuery);
    $stmt->execute();
    $programs = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    if (count($programs) > 0) {
        echo "Found " . count($programs) . " programs to use for sample courses\n";
        
        // Sample course data
        $sampleCourses = [
            [
                'course_code' => 'IT101',
                'course_title' => 'Introduction to Information Technology',
                'units' => 3,
                'term' => '1st Semester',
                'academic_year' => 'A.Y. 2025-2026',
                'year_level' => '1st Year'
            ],
            [
                'course_code' => 'CS101',
                'course_title' => 'Introduction to Computer Science',
                'units' => 3,
                'term' => '1st Semester',
                'academic_year' => 'A.Y. 2025-2026',
                'year_level' => '1st Year'
            ],
            [
                'course_code' => 'MATH101',
                'course_title' => 'Calculus I',
                'units' => 4,
                'term' => '1st Semester',
                'academic_year' => 'A.Y. 2025-2026',
                'year_level' => '1st Year'
            ],
            [
                'course_code' => 'ENG101',
                'course_title' => 'English Communication',
                'units' => 3,
                'term' => '1st Semester',
                'academic_year' => 'A.Y. 2025-2026',
                'year_level' => '1st Year'
            ],
            [
                'course_code' => 'IT102',
                'course_title' => 'Programming Fundamentals',
                'units' => 3,
                'term' => '2nd Semester',
                'academic_year' => 'A.Y. 2025-2026',
                'year_level' => '1st Year'
            ]
        ];
        
        $insertQuery = "
            INSERT INTO courses (course_code, course_title, units, program_id, term, academic_year, year_level, status) 
            VALUES (?, ?, ?, ?, ?, ?, ?, 'Active')
        ";
        $stmt = $pdo->prepare($insertQuery);
        
        $coursesAdded = 0;
        foreach ($sampleCourses as $index => $course) {
            // Assign courses to different programs
            $programId = $programs[$index % count($programs)]['id'];
            
            $stmt->execute([
                $course['course_code'],
                $course['course_title'],
                $course['units'],
                $programId,
                $course['term'],
                $course['academic_year'],
                $course['year_level']
            ]);
            
            $coursesAdded++;
            echo "✅ Added course: {$course['course_code']} - {$course['course_title']} (Program ID: $programId)\n";
        }
        
        echo "\n✅ Added $coursesAdded sample courses!\n";
        
    } else {
        echo "⚠️ No programs found. Please create some programs first.\n";
    }
    
    // Show final count
    $countQuery = "SELECT COUNT(*) as count FROM courses";
    $stmt = $pdo->prepare($countQuery);
    $stmt->execute();
    $result = $stmt->fetch(PDO::FETCH_ASSOC);
    
    echo "\n=== Summary ===\n";
    echo "Total courses in database: " . $result['count'] . "\n";
    echo "✅ Courses table setup complete!\n";
    echo "\nYou can now:\n";
    echo "1. Use the 'Add New Course' form to create more courses\n";
    echo "2. View courses in the 'All Courses' page\n";
    echo "3. Manage courses through the course management interface\n";
    
} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
}
?>
