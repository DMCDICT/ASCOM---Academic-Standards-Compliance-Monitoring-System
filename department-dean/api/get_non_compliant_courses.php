<?php
// get_non_compliant_courses.php
// API endpoint to fetch non-compliant courses for the dean's department

header('Content-Type: application/json');
session_start();
require_once dirname(__FILE__) . '/../includes/db_connection.php';

$response = ['success' => false, 'data' => [], 'message' => ''];

try {
    // Get dean's department from session
    $departmentCode = $_SESSION['selected_role']['department_code'] ?? null;
    
    if (!$departmentCode) {
        throw new Exception('Department not identified');
    }
    
    // Get department ID from code
    $deptStmt = $pdo->prepare("SELECT id FROM departments WHERE department_code = ? LIMIT 1");
    $deptStmt->execute([$departmentCode]);
    $deptRow = $deptStmt->fetch(PDO::FETCH_ASSOC);
    $departmentId = $deptRow['id'] ?? null;
    
    if (!$departmentId) {
        throw new Exception('Department not found');
    }
    
    $currentYear = date('Y');
    $fiveYearsAgo = $currentYear - 4; // Books from last 5 years are compliant
    
    // Get courses with compliant book count
    $query = "
        SELECT 
            c.id,
            c.course_code,
            c.course_title,
            p.program_code,
            p.program_name,
            COUNT(br.id) as total_references,
            COUNT(CASE WHEN br.publication_year >= ? AND br.publication_year IS NOT NULL THEN 1 END) as compliant_count
        FROM courses c
        LEFT JOIN programs p ON c.program_id = p.id
        LEFT JOIN book_references br ON c.id = br.course_id
        WHERE c.program_id IN (
            SELECT id FROM programs WHERE department_id = ?
        )
        GROUP BY c.id, c.course_code, c.course_title, p.program_code, p.program_name
        HAVING compliant_count < 5 OR total_references = 0
        ORDER BY c.course_code ASC
    ";
    
    $stmt = $pdo->prepare($query);
    $stmt->execute([$fiveYearsAgo, $departmentId]);
    $courses = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Format the response
    $formattedCourses = array_map(function($course) {
        $compliantCount = intval($course['compliant_count'] ?? 0);
        $neededCount = max(0, 5 - $compliantCount);
        
        return [
            'id' => $course['id'],
            'course_code' => $course['course_code'],
            'course_title' => $course['course_title'],
            'program_code' => $course['program_code'],
            'program_name' => $course['program_name'],
            'total_references' => intval($course['total_references']),
            'compliant_count' => $compliantCount,
            'needed_count' => $neededCount,
            'is_compliant' => $compliantCount >= 5
        ];
    }, $courses);
    
    $response['success'] = true;
    $response['data'] = $formattedCourses;
    $response['message'] = 'Non-compliant courses fetched successfully';
    
} catch (Exception $e) {
    $response['message'] = 'Failed to fetch courses: ' . $e->getMessage();
}

echo json_encode($response);
exit;