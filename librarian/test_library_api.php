<?php
// test_library_api.php - Test the library API
error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "Testing Library API...\n";

try {
    $pdo = new PDO('mysql:host=localhost;dbname=ascom_db', 'root', '');
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    echo "✅ Database connection successful\n";
    
    // Test the query
    $query = "
        SELECT 
            c.id,
            c.course_code,
            c.course_title,
            c.units,
            c.year_level,
            c.term,
            c.academic_year,
            c.status,
            c.created_at,
            COALESCE(d.department_name, 'N/A') as department_name,
            COALESCE(p.program_name, 'N/A') as program_name
        FROM courses c
        LEFT JOIN departments d ON c.program_id = d.id
        LEFT JOIN programs p ON c.program_id = p.id
        WHERE 1=1
        ORDER BY c.created_at DESC
        LIMIT 10 OFFSET 0
    ";
    
    echo "📋 Query: " . $query . "\n";
    
    $stmt = $pdo->prepare($query);
    $stmt->execute();
    $courses = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo "📈 Found " . count($courses) . " courses\n";
    
    foreach ($courses as $course) {
        echo "  - " . $course['course_code'] . ": " . $course['course_title'] . " (Dept: " . $course['department_name'] . ", Program: " . $course['program_name'] . ")\n";
    }
    
    // Test JSON response
    $formattedCourses = [];
    foreach ($courses as $course) {
        $formattedCourses[] = [
            'id' => $course['id'],
            'course_code' => $course['course_code'],
            'course_title' => $course['course_title'],
            'units' => $course['units'],
            'year_level' => $course['year_level'],
            'term' => $course['term'],
            'academic_year' => $course['academic_year'],
            'department' => $course['department_name'],
            'program' => $course['program_name'],
            'status' => $course['status'],
            'created_at' => date('M d, Y', strtotime($course['created_at']))
        ];
    }
    
    echo "📝 JSON Response:\n";
    echo json_encode([
        'success' => true,
        'data' => $formattedCourses,
        'pagination' => [
            'currentPage' => 1,
            'totalPages' => 1,
            'totalRecords' => count($courses),
            'hasNextPage' => false,
            'hasPrevPage' => false,
            'limit' => 10
        ]
    ], JSON_PRETTY_PRINT);
    
} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
    echo "Stack trace: " . $e->getTraceAsString() . "\n";
}
?>
