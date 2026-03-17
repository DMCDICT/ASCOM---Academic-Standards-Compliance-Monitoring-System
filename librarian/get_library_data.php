<?php
// get_library_data.php - Fetch library data from database
// session_start(); // Removed to avoid header issues

// Database connection
if (getenv('DOCKER_ENV') === 'true' || file_exists('/.dockerenv')) {
    $servername = "db";
} else {
    $servername = "localhost";
}
$username = "root";
$password = "";
$database = "ascom_db";

try {
    $pdo = new PDO("mysql:host=$servername;dbname=$database", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch(PDOException $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Database connection failed: ' . $e->getMessage()]);
    exit;
}

// Get filter parameters
$yearLevel = $_GET['yearLevel'] ?? 'all';
$academicTerm = $_GET['academicTerm'] ?? 'all';
$department = $_GET['department'] ?? 'all';
$programs = $_GET['programs'] ?? 'all';


// Handle JSON string for programs
if (is_string($programs) && $programs !== 'all') {
    $programs = json_decode($programs, true);
    if (!is_array($programs)) {
        $programs = 'all';
    }
}

// Build the base query
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

// Apply filters
if ($yearLevel !== 'all') {
    $query .= " AND c.year_level = ?";
    $params[] = $yearLevel;
}

if ($academicTerm !== 'all') {
    $query .= " AND c.term = ?";
    $params[] = $academicTerm;
}

if ($department !== 'all') {
    $query .= " AND d.department_code = ?";
    $params[] = $department;
}

if ($programs !== 'all' && is_array($programs) && !in_array('all', $programs)) {
    $placeholders = str_repeat('?,', count($programs) - 1) . '?';
    $query .= " AND p.program_code IN ($placeholders)";
    $params = array_merge($params, $programs);
}

// Add ordering - newest to oldest by default
$query .= " ORDER BY c.created_at DESC, c.year_level ASC, c.term ASC, c.course_code ASC, p.program_code ASC";

// Get all courses without pagination - we'll handle pagination in JavaScript after merging

// Get total count - build a separate count query with same filters
$countQuery = "
    SELECT COUNT(*)
    FROM courses c
    LEFT JOIN programs p ON c.program_id = p.id
    LEFT JOIN departments d ON p.department_id = d.id
    WHERE 1=1
";

// Apply same filters to count query
if ($yearLevel !== 'all') {
    $countQuery .= " AND c.year_level = ?";
}

if ($academicTerm !== 'all') {
    $countQuery .= " AND c.term = ?";
}

if ($department !== 'all') {
    $countQuery .= " AND d.department_code = ?";
}

if ($programs !== 'all' && is_array($programs) && !in_array('all', $programs)) {
    $placeholders = str_repeat('?,', count($programs) - 1) . '?';
    $countQuery .= " AND p.program_code IN ($placeholders)";
}

try {
    $countStmt = $pdo->prepare($countQuery);
    $countStmt->execute($params);
    $totalRecords = $countStmt->fetchColumn();
} catch(PDOException $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Count query failed: ' . $e->getMessage()]);
    exit;
}

// No pagination at database level - get all courses for merging

try {
    $stmt = $pdo->prepare($query);
    $stmt->execute($params);
    $courses = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch(PDOException $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Query failed: ' . $e->getMessage()]);
    exit;
}

// Format the data for the frontend
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
        'academic_year_label' => $course['academic_year_label'] ?? 'N/A',
        'program_color' => $course['program_color'] ?? '#1976d2',
        'department' => $course['department_name'] ?? 'N/A',
        'program' => $course['program_code'] ?? 'N/A',
        'program_code' => $course['program_code'] ?? 'N/A',
        'status' => $course['status'],
        'book_count' => (int)$course['book_count'],
        'compliant_book_count' => (int)$course['compliant_book_count'],
        'created_at' => date('M d, Y', strtotime($course['created_at']))
    ];
}

// Calculate pagination info
// Return JSON response with all courses (no pagination at database level)
echo json_encode([
    'success' => true,
    'data' => $formattedCourses
]);
?>
