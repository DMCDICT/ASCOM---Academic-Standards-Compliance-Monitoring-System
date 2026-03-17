<?php
// Test script for all courses functionality
session_start();
require_once 'department-dean/includes/db_connection.php';

echo "<h2>All Courses Debug Test</h2>";

// Check session data
echo "<h3>Session Data:</h3>";
echo "<pre>";
print_r($_SESSION);
echo "</pre>";

// Check database connection
echo "<h3>Database Connection:</h3>";
if (isset($pdo)) {
    echo "✅ PDO connection exists<br>";
    
    // Check if courses table exists
    try {
        $query = "SHOW TABLES LIKE 'courses'";
        $stmt = $pdo->prepare($query);
        $stmt->execute();
        $tableExists = $stmt->rowCount() > 0;
        
        if ($tableExists) {
            echo "✅ Courses table exists<br>";
            
            // Check table structure
            $query = "DESCRIBE courses";
            $stmt = $pdo->prepare($query);
            $stmt->execute();
            $columns = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            echo "<h4>Courses table structure:</h4>";
            echo "<pre>";
            foreach ($columns as $column) {
                echo "- " . $column['Field'] . " (" . $column['Type'] . ")\n";
            }
            echo "</pre>";
            
            // Check if there are any courses
            $query = "SELECT COUNT(*) as count FROM courses";
            $stmt = $pdo->prepare($query);
            $stmt->execute();
            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            
            echo "<p>Total courses in database: " . $result['count'] . "</p>";
            
            if ($result['count'] > 0) {
                // Show sample courses
                $query = "SELECT * FROM courses LIMIT 5";
                $stmt = $pdo->prepare($query);
                $stmt->execute();
                $courses = $stmt->fetchAll(PDO::FETCH_ASSOC);
                
                echo "<h4>Sample courses:</h4>";
                echo "<pre>";
                print_r($courses);
                echo "</pre>";
            }
            
        } else {
            echo "❌ Courses table does not exist<br>";
        }
        
    } catch (Exception $e) {
        echo "❌ Error checking courses table: " . $e->getMessage() . "<br>";
    }
    
    // Check departments table
    try {
        $query = "SELECT * FROM departments";
        $stmt = $pdo->prepare($query);
        $stmt->execute();
        $departments = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        echo "<h4>Departments:</h4>";
        echo "<pre>";
        print_r($departments);
        echo "</pre>";
        
    } catch (Exception $e) {
        echo "❌ Error checking departments: " . $e->getMessage() . "<br>";
    }
    
    // Check programs table
    try {
        $query = "SELECT * FROM programs";
        $stmt = $pdo->prepare($query);
        $stmt->execute();
        $programs = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        echo "<h4>Programs:</h4>";
        echo "<pre>";
        print_r($programs);
        echo "</pre>";
        
    } catch (Exception $e) {
        echo "❌ Error checking programs: " . $e->getMessage() . "<br>";
    }
    
} else {
    echo "❌ PDO connection not found<br>";
}

// Test the actual query from all-courses.php
if (isset($pdo) && isset($_SESSION['selected_role']['department_code'])) {
    echo "<h3>Testing All Courses Query:</h3>";
    
    $deanDepartmentCode = $_SESSION['selected_role']['department_code'];
    echo "<p>Dean Department Code: " . $deanDepartmentCode . "</p>";
    
    try {
        $query = "
            SELECT 
                c.course_code,
                c.course_title,
                c.units,
                p.program_code,
                p.program_name,
                p.color_code as program_color,
                CONCAT(u.user_title, ' ', u.user_first_name, ' ', u.user_last_name) AS faculty_name,
                c.status,
                c.term,
                c.academic_year,
                c.year_level
            FROM 
                courses c
            JOIN 
                programs p ON c.program_id = p.id
            JOIN
                departments d ON p.department_id = d.id
            LEFT JOIN 
                users u ON c.faculty_id = u.id AND u.role = 'faculty' AND u.is_active = TRUE
            WHERE 
                d.department_code = ?
            ORDER BY 
                p.program_code ASC, c.course_code ASC;
        ";
        
        $stmt = $pdo->prepare($query);
        $stmt->execute([$deanDepartmentCode]);
        $result = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        echo "<p>Query returned " . count($result) . " courses</p>";
        
        if (count($result) > 0) {
            echo "<h4>Query Results:</h4>";
            echo "<pre>";
            print_r($result);
            echo "</pre>";
        } else {
            echo "<p style='color: orange;'>⚠️ No courses found for department: " . $deanDepartmentCode . "</p>";
        }
        
    } catch (Exception $e) {
        echo "<p style='color: red;'>❌ Query error: " . $e->getMessage() . "</p>";
    }
}
?>
