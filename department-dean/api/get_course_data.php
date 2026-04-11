<?php
// API endpoint to get course data for editing
ob_start();
header('Content-Type: application/json');

// Include session configuration
$sessionConfigPath = dirname(dirname(__FILE__)) . '/../session_config.php';
if (!file_exists($sessionConfigPath)) {
    throw new Exception('Session configuration file not found');
}
require_once $sessionConfigPath;

// Start session
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'Unauthorized access']);
    exit;
}

// Include database connection
require_once dirname(__FILE__) . '/../../includes/db_connection.php';

try {
    $courseId = intval($_GET['course_id'] ?? 0);
    
    if ($courseId <= 0) {
        echo json_encode(['success' => false, 'message' => 'Invalid course ID']);
        exit;
    }
    
    // Get dean's department code
    $deanDepartmentCode = $_SESSION['selected_role']['department_code'] ?? null;
    
    if (!$deanDepartmentCode) {
        echo json_encode(['success' => false, 'message' => 'Unable to determine department']);
        exit;
    }
    
    // Fetch course data
    $courseQuery = "
        SELECT 
            c.id,
            c.course_code,
            c.course_title,
            c.units,
            c.program_id,
            c.status,
            c.term,
            COALESCE(sy.school_year_label, c.academic_year) as academic_year,
            c.year_level,
            p.program_code,
            p.program_name,
            d.color_code
        FROM courses c
        LEFT JOIN programs p ON c.program_id = p.id
        LEFT JOIN departments d ON p.department_id = d.id
        LEFT JOIN school_years sy ON c.academic_year = sy.id
        WHERE c.id = ? 
        AND d.department_code = ?
    ";
    
    $courseStmt = $pdo->prepare($courseQuery);
    $courseStmt->execute([$courseId, $deanDepartmentCode]);
    $courseData = $courseStmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$courseData) {
        echo json_encode(['success' => false, 'message' => 'Course not found or you do not have permission']);
        exit;
    }
    
    // Fetch all programs for this course
    $programs = [];
    
    // Try course_programs table first
    $programsQuery = "
        SELECT p.id, p.program_code, p.program_name, d.color_code
        FROM course_programs cp
        JOIN programs p ON cp.program_id = p.id
        JOIN departments d ON p.department_id = d.id
        WHERE cp.course_code = ?
        AND d.department_code = ?
        ORDER BY p.program_code ASC
    ";
    
    $programsStmt = $pdo->prepare($programsQuery);
    $programsStmt->execute([$courseData['course_code'], $deanDepartmentCode]);
    $programs = $programsStmt->fetchAll(PDO::FETCH_ASSOC);
    
    // If no programs found in course_programs, use the program from the course record
    if (empty($programs) && $courseData['program_id']) {
        $programsQuery = "
            SELECT p.id, p.program_code, p.program_name, d.color_code
            FROM programs p
            JOIN departments d ON p.department_id = d.id
            WHERE p.id = ?
            AND d.department_code = ?
        ";
        
        $programsStmt = $pdo->prepare($programsQuery);
        $programsStmt->execute([$courseData['program_id'], $deanDepartmentCode]);
        $programs = $programsStmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    // Format programs for the modal
    $formattedPrograms = [];
    foreach ($programs as $program) {
        $formattedPrograms[] = [
            'id' => $program['id'],
            'program_code' => $program['program_code'],
            'program_name' => $program['program_name'],
            'program_color' => $program['color_code'] ?? '#1976d2'
        ];
    }
    
    // Return formatted course data
    echo json_encode([
        'success' => true,
        'course' => [
            'course_code' => $courseData['course_code'],
            'course_title' => $courseData['course_title'],
            'units' => $courseData['units'],
            'term' => $courseData['term'] ?? '',
            'academic_year' => $courseData['academic_year'] ?? '',
            'year_level' => $courseData['year_level'] ?? '',
            'programs' => $formattedPrograms
        ]
    ]);
    
} catch (Exception $e) {
    ob_clean();
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}

ob_end_flush();
?>

