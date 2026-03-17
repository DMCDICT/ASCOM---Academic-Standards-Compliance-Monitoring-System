<?php
// Simple test page to verify program-courses functionality
require_once dirname(__FILE__) . '/includes/db_connection.php';

// Get the program code from URL parameter
$programCode = $_GET['program'] ?? '';

echo "<!DOCTYPE html>
<html>
<head>
    <title>Test Program Courses</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 20px; }
        .debug { background: #ffeb3b; padding: 10px; margin: 10px; border: 2px solid #f57f17; }
        .error { background: #ffcdd2; padding: 10px; margin: 10px; border: 2px solid #f44336; }
        .success { background: #c8e6c9; padding: 10px; margin: 10px; border: 2px solid #4caf50; }
        table { border-collapse: collapse; width: 100%; margin-top: 20px; }
        th, td { border: 1px solid #ddd; padding: 8px; text-align: left; }
        th { background-color: #f2f2f2; }
    </style>
</head>
<body>
    <h1>Test Program Courses Page</h1>";

// Debug information
echo "<div class='debug'>";
echo "<strong>DEBUG INFO:</strong><br>";
echo "Program Code: " . htmlspecialchars($programCode) . "<br>";
echo "Department Code: " . htmlspecialchars($_SESSION['selected_role']['department_code'] ?? 'null') . "<br>";
echo "URL: " . htmlspecialchars($_SERVER['REQUEST_URI'] ?? 'unknown') . "<br>";
echo "</div>";

if (empty($programCode)) {
    echo "<div class='error'>";
    echo "<strong>ERROR: No program code provided!</strong><br>";
    echo "Please add ?program=BSIT to the URL<br>";
    echo "</div>";
    echo "<p><a href='test_program_courses.php?program=BSIT'>Test with BSIT</a></p>";
    echo "</body></html>";
    exit;
}

$deanDepartmentCode = $_SESSION['selected_role']['department_code'] ?? null;
if (empty($deanDepartmentCode)) {
    echo "<div class='error'>";
    echo "<strong>ERROR: No department code in session!</strong><br>";
    echo "Please log in first<br>";
    echo "</div>";
    echo "</body></html>";
    exit;
}

// Test database connection and query
try {
    $query = "
        SELECT 
            c.course_code,
            c.course_title,
            c.units,
            d.color_code as program_color,
            CONCAT(u.first_name, ' ', u.last_name) AS faculty_name,
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
            users u ON c.faculty_id = u.id AND u.is_active = TRUE
        LEFT JOIN 
            user_roles ur ON u.id = ur.user_id AND ur.role_name = 'teacher' AND ur.is_active = 1
        WHERE 
            d.department_code = ? AND p.program_code = ?
        ORDER BY 
            c.course_code ASC;
    ";
    
    $stmt = $pdo->prepare($query);
    $stmt->execute([$deanDepartmentCode, $programCode]);
    $courses = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo "<div class='success'>";
    echo "<strong>SUCCESS: Database query executed!</strong><br>";
    echo "Found " . count($courses) . " courses for program: " . htmlspecialchars($programCode) . "<br>";
    echo "</div>";
    
    if (count($courses) > 0) {
        echo "<h2>Courses for Program: " . htmlspecialchars($programCode) . "</h2>";
        echo "<table>";
        echo "<tr><th>Course Code</th><th>Course Title</th><th>Units</th><th>Faculty</th><th>Status</th><th>Term</th><th>Year Level</th></tr>";
        
        foreach ($courses as $course) {
            echo "<tr>";
            echo "<td>" . htmlspecialchars($course['course_code']) . "</td>";
            echo "<td>" . htmlspecialchars($course['course_title']) . "</td>";
            echo "<td>" . htmlspecialchars($course['units']) . "</td>";
            echo "<td>" . htmlspecialchars($course['faculty_name'] ?? 'Unassigned') . "</td>";
            echo "<td>" . htmlspecialchars($course['status']) . "</td>";
            echo "<td>" . htmlspecialchars($course['term']) . "</td>";
            echo "<td>" . htmlspecialchars($course['year_level']) . "</td>";
            echo "</tr>";
        }
        echo "</table>";
    } else {
        echo "<div class='error'>";
        echo "<strong>No courses found for this program!</strong><br>";
        echo "This might be because:<br>";
        echo "1. The program has no courses assigned<br>";
        echo "2. The program code is incorrect<br>";
        echo "3. There's a database issue<br>";
        echo "</div>";
    }
    
} catch (Exception $e) {
    echo "<div class='error'>";
    echo "<strong>DATABASE ERROR:</strong><br>";
    echo htmlspecialchars($e->getMessage());
    echo "</div>";
}

echo "<p><a href='content.php?page=dashboard'>Back to Dashboard</a></p>";
echo "</body></html>";
?>
