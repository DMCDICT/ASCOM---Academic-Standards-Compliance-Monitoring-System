<?php
// check_and_create_tables.php
// Script to check existing tables and create missing ones

require_once 'department-dean/includes/db_connection.php';

echo "<h2>Database Table Check and Creation</h2>";

try {
    // Check what tables exist
    $tablesQuery = "SHOW TABLES";
    $stmt = $pdo->prepare($tablesQuery);
    $stmt->execute();
    $existingTables = $stmt->fetchAll(PDO::FETCH_COLUMN);
    
    echo "<h3>Existing Tables:</h3>";
    echo "<ul>";
    foreach ($existingTables as $table) {
        echo "<li>$table</li>";
    }
    echo "</ul>";
    
    // Check if we need to create tables
    $requiredTables = ['departments', 'programs', 'courses', 'users'];
    $missingTables = array_diff($requiredTables, $existingTables);
    
    if (empty($missingTables)) {
        echo "<p style='color: green;'>✅ All required tables exist!</p>";
    } else {
        echo "<h3>Missing Tables:</h3>";
        echo "<ul>";
        foreach ($missingTables as $table) {
            echo "<li style='color: red;'>❌ $table</li>";
        }
        echo "</ul>";
        
        echo "<h3>Creating Missing Tables...</h3>";
        
        // Create departments table
        if (in_array('departments', $missingTables)) {
            echo "<p>Creating departments table...</p>";
            $createDepartments = "
                CREATE TABLE departments (
                    id INT AUTO_INCREMENT PRIMARY KEY,
                    department_code VARCHAR(10) UNIQUE NOT NULL,
                    department_name VARCHAR(100) NOT NULL,
                    color_code VARCHAR(7) DEFAULT '#1976d2',
                    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
                )
            ";
            $pdo->exec($createDepartments);
            echo "<p style='color: green;'>✅ Departments table created</p>";
        }
        
        // Create programs table
        if (in_array('programs', $missingTables)) {
            echo "<p>Creating programs table...</p>";
            $createPrograms = "
                CREATE TABLE programs (
                    id INT AUTO_INCREMENT PRIMARY KEY,
                    program_code VARCHAR(10) NOT NULL,
                    program_name VARCHAR(200) NOT NULL,
                    major VARCHAR(100) NULL,
                    color_code VARCHAR(7) DEFAULT '#1976d2',
                    description TEXT,
                    department_id INT,
                    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                    FOREIGN KEY (department_id) REFERENCES departments(id),
                    UNIQUE KEY unique_program_dept (program_code, department_id)
                )
            ";
            $pdo->exec($createPrograms);
            echo "<p style='color: green;'>✅ Programs table created</p>";
        }
        
        // Create courses table
        if (in_array('courses', $missingTables)) {
            echo "<p>Creating courses table...</p>";
            $createCourses = "
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
                    FOREIGN KEY (program_id) REFERENCES programs(id),
                    UNIQUE KEY unique_course_program (course_code, program_id)
                )
            ";
            $pdo->exec($createCourses);
            echo "<p style='color: green;'>✅ Courses table created</p>";
        }
        
        // Create users table if missing
        if (in_array('users', $missingTables)) {
            echo "<p>Creating users table...</p>";
            $createUsers = "
                CREATE TABLE users (
                    id INT AUTO_INCREMENT PRIMARY KEY,
                    username VARCHAR(50) UNIQUE NOT NULL,
                    user_first_name VARCHAR(50) NOT NULL,
                    user_last_name VARCHAR(50) NOT NULL,
                    user_title VARCHAR(20) DEFAULT 'Prof.',
                    email VARCHAR(100),
                    password VARCHAR(255),
                    role ENUM('admin', 'dean', 'faculty', 'student') DEFAULT 'faculty',
                    department_id INT NULL,
                    program_id INT NULL,
                    is_active BOOLEAN DEFAULT TRUE,
                    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                    FOREIGN KEY (department_id) REFERENCES departments(id),
                    FOREIGN KEY (program_id) REFERENCES programs(id)
                )
            ";
            $pdo->exec($createUsers);
            echo "<p style='color: green;'>✅ Users table created</p>";
        }
    }
    
    // Add sample data if tables are empty
    echo "<h3>Checking for Sample Data...</h3>";
    
    // Check departments
    $deptCount = $pdo->query("SELECT COUNT(*) FROM departments")->fetchColumn();
    if ($deptCount == 0) {
        echo "<p>Adding sample departments...</p>";
        $insertDepartments = "
            INSERT INTO departments (department_code, department_name, color_code) VALUES
            ('CCS', 'College of Computing Studies', '#1976d2'),
            ('CBE', 'College of Business Education', '#4CAF50'),
            ('CET', 'College of Engineering and Technology', '#FF9800'),
            ('CAS', 'College of Arts and Sciences', '#9C27B0')
        ";
        $pdo->exec($insertDepartments);
        echo "<p style='color: green;'>✅ Sample departments added</p>";
    } else {
        echo "<p>Departments table has $deptCount records</p>";
    }
    
    // Check programs
    $progCount = $pdo->query("SELECT COUNT(*) FROM programs")->fetchColumn();
    if ($progCount == 0) {
        echo "<p>Adding sample programs...</p>";
        $insertPrograms = "
            INSERT INTO programs (program_code, program_name, major, color_code, description, department_id) VALUES
            ('BSIT', 'Bachelor of Science in Information Technology', 'Software Engineering', '#4A7AF2', 'Comprehensive IT program covering software development, networking, and system administration.', (SELECT id FROM departments WHERE department_code = 'CCS')),
            ('BSCS', 'Bachelor of Science in Computer Science', 'Data Science', '#14A338', 'Advanced computer science program focusing on algorithms, data structures, and software engineering.', (SELECT id FROM departments WHERE department_code = 'CCS')),
            ('BSIS', 'Bachelor of Science in Information Systems', 'Business Analytics', '#E6AA28', 'Business-oriented IT program combining technology with management principles.', (SELECT id FROM departments WHERE department_code = 'CCS')),
            ('BLIS', 'Bachelor of Library and Information Science', NULL, '#CD2323', 'Specialized program in library management and information organization.', (SELECT id FROM departments WHERE department_code = 'CCS')),
            ('BSBA', 'Bachelor of Science in Business Administration', 'Marketing Management', '#9C27B0', 'Comprehensive business program covering management, marketing, and finance.', (SELECT id FROM departments WHERE department_code = 'CBE')),
            ('BSE', 'Bachelor of Science in Education', 'Elementary Education', '#FF9800', 'Teacher education program preparing future educators for various subjects.', (SELECT id FROM departments WHERE department_code = 'CBE'))
        ";
        $pdo->exec($insertPrograms);
        echo "<p style='color: green;'>✅ Sample programs added</p>";
    } else {
        echo "<p>Programs table has $progCount records</p>";
    }
    
    // Check courses
    $courseCount = $pdo->query("SELECT COUNT(*) FROM courses")->fetchColumn();
    if ($courseCount == 0) {
        echo "<p>Adding sample courses...</p>";
        $insertCourses = "
            INSERT INTO courses (course_code, course_title, units, program_id, status, term, academic_year, year_level) VALUES
            ('IT101', 'Introduction to Information Technology', 3, (SELECT id FROM programs WHERE program_code = 'BSIT'), 'Active', '1st Semester', 'A.Y. 2025-2026', '1st Year'),
            ('IT201', 'Web Development Fundamentals', 3, (SELECT id FROM programs WHERE program_code = 'BSIT'), 'Active', '2nd Semester', 'A.Y. 2025-2026', '2nd Year'),
            ('IT301', 'Database Management Systems', 3, (SELECT id FROM programs WHERE program_code = 'BSIT'), 'Active', '1st Semester', 'A.Y. 2025-2026', '3rd Year'),
            ('CS101', 'Introduction to Computer Science', 3, (SELECT id FROM programs WHERE program_code = 'BSCS'), 'Active', '1st Semester', 'A.Y. 2025-2026', '1st Year'),
            ('CS201', 'Data Structures and Algorithms', 3, (SELECT id FROM programs WHERE program_code = 'BSCS'), 'Active', '2nd Semester', 'A.Y. 2025-2026', '2nd Year'),
            ('BA101', 'Introduction to Business Administration', 3, (SELECT id FROM programs WHERE program_code = 'BSBA'), 'Active', '1st Semester', 'A.Y. 2025-2026', '1st Year'),
            ('BA201', 'Marketing Principles', 3, (SELECT id FROM programs WHERE program_code = 'BSBA'), 'Active', '2nd Semester', 'A.Y. 2025-2026', '2nd Year'),
            ('ED101', 'Foundations of Education', 3, (SELECT id FROM programs WHERE program_code = 'BSE'), 'Active', '1st Semester', 'A.Y. 2025-2026', '1st Year'),
            ('ED201', 'Teaching Methods', 3, (SELECT id FROM programs WHERE program_code = 'BSE'), 'Active', '2nd Semester', 'A.Y. 2025-2026', '2nd Year')
        ";
        $pdo->exec($insertCourses);
        echo "<p style='color: green;'>✅ Sample courses added</p>";
    } else {
        echo "<p>Courses table has $courseCount records</p>";
    }
    
    echo "<h3>Final Status:</h3>";
    echo "<p style='color: green; font-weight: bold;'>✅ Database setup complete! You can now test the course filtering.</p>";
    
} catch (PDOException $e) {
    echo "<p style='color: red;'>❌ Error: " . $e->getMessage() . "</p>";
}
?>
