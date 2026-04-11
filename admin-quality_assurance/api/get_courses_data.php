<?php
// get_courses_data.php - Fetch courses data from database for Quality Assurance

// Set JSON header first, before any output
header('Content-Type: application/json');

// Prevent any output before JSON
ob_start();

try {
    require_once dirname(__FILE__) . '/../includes/db_connection.php';
} catch (Exception $e) {
    ob_end_clean();
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Database connection failed: ' . $e->getMessage()]);
    exit;
}

// NO FILTERING - Quality Assurance shows ALL courses (both compliant and non-compliant)
// All filter parameters removed - fetch everything from database

// Build the base query - NO FILTERS APPLIED
$query = "
    SELECT 
        c.id,
        c.course_code,
        c.course_title,
        c.units,
        c.year_level,
        c.term,
        c.academic_year,
        COALESCE(sy.school_year_label, CONCAT('A.Y. ', sy.year_start, ' - ', sy.year_end)) as academic_year_label,
        c.status,
        c.created_at,
        COALESCE(d.department_name, 'N/A') as department_name,
        COALESCE(d.department_code, 'N/A') as department_code,
        COALESCE(p.program_code, 'N/A') as program_code,
        COALESCE(p.color_code, '#1976d2') as program_color,
        COALESCE(book_counts.book_count, 0) as book_count,
        COALESCE(compliant_counts.compliant_count, 0) as compliant_book_count
    FROM courses c
    LEFT JOIN programs p ON c.program_id = p.id
    LEFT JOIN departments d ON p.department_id = d.id
    LEFT JOIN school_years sy ON c.academic_year = sy.id
    LEFT JOIN (
        SELECT course_id, COUNT(*) as book_count 
        FROM book_references 
        GROUP BY course_id
    ) book_counts ON c.id = book_counts.course_id
    LEFT JOIN (
        SELECT 
            course_id, 
            COUNT(*) AS compliant_count
        FROM book_references
        WHERE (YEAR(CURDATE()) - CAST(publication_year AS UNSIGNED)) < 5
        GROUP BY course_id
    ) compliant_counts ON c.id = compliant_counts.course_id
    WHERE 1=1
";

$params = [];

// NO FILTERS - Show ALL courses
// Add ordering - newest to oldest by default
$query .= " ORDER BY c.created_at DESC, c.year_level ASC, c.term ASC, c.course_code ASC, p.program_code ASC";

try {
    $stmt = $pdo->prepare($query);
    $stmt->execute($params);
    $courses = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch(PDOException $e) {
    ob_end_clean();
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Query failed: ' . $e->getMessage()]);
    exit;
}

// Format the data for the frontend
$formattedCourses = [];
foreach ($courses as $course) {
    // Calculate compliance status
    // Note: The column is aliased as 'compliant_book_count' in the SQL query
    $compliantCount = (int)$course['compliant_book_count'];
    $isCompliant = $compliantCount >= 5;
    $compliancePercentage = $isCompliant ? 100 : min(100, round(($compliantCount / 5) * 100));
    
    // Calculate priority based on compliance
    $priority = 'Low';
    if ($isCompliant) {
        $priority = 'Cleared';
    } else if ($compliancePercentage < 40) {
        $priority = 'High';
    } else if ($compliancePercentage < 80) {
        $priority = 'Medium';
    }
    
    $formattedCourses[] = [
        'id' => $course['id'],
        'course_code' => $course['course_code'],
        'course_title' => $course['course_title'],
        'units' => $course['units'],
        'year_level' => $course['year_level'],
        'term' => $course['term'],
        'academic_year' => $course['academic_year'],
        'academic_year_label' => $course['academic_year_label'] ?? 'N/A',
        'program_color' => $course['program_color'] ?? '#1976d2',
        'department' => $course['department_name'] ?? 'N/A',
        'department_code' => $course['department_code'] ?? 'N/A',
        'program' => $course['program_code'] ?? 'N/A',
        'program_code' => $course['program_code'] ?? 'N/A',
        'status' => $isCompliant ? 'Compliant' : 'Non-Compliant',
        'book_count' => (int)$course['book_count'],
        'compliant_book_count' => $compliantCount,
        'compliance_percentage' => $compliancePercentage,
        'priority' => $priority,
        'created_at' => date('M d, Y', strtotime($course['created_at']))
    ];
}

// NO STATUS FILTERING - Quality Assurance shows ALL courses (both compliant and non-compliant)
// This matches Library Management behavior - no status filter parameter exists
// Quality Assurance needs to monitor ALL courses, so we never filter by compliance status
$compliantCount = count(array_filter($formattedCourses, function($c) { return $c['status'] === 'Compliant'; }));
$nonCompliantCount = count(array_filter($formattedCourses, function($c) { return $c['status'] === 'Non-Compliant'; }));

// NO PRIORITY FILTERING - Show ALL courses

// Clean any output buffer and return JSON response
ob_end_clean();
echo json_encode([
    'success' => true,
    'data' => $formattedCourses
]);
exit;
?>

