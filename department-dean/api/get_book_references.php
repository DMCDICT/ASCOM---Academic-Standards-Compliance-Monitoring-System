<?php
// API endpoint to fetch book references for a course
header('Content-Type: application/json');

// Include database connection
require_once dirname(__FILE__) . '/../includes/db_connection.php';

// Check if course_code is provided
if (!isset($_GET['course_code'])) {
    http_response_code(400);
    echo json_encode([
        'status' => 'error',
        'message' => 'Course code is required'
    ]);
    exit;
}

$courseCode = $_GET['course_code'];

try {
    // First, get the course ID from the course code
    $courseQuery = "SELECT id FROM courses WHERE course_code = ? LIMIT 1";
    $courseStmt = $pdo->prepare($courseQuery);
    $courseStmt->execute([$courseCode]);
    $course = $courseStmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$course) {
        echo json_encode([
            'status' => 'success',
            'references' => [],
            'message' => 'Course not found'
        ]);
        exit;
    }
    
    $courseId = $course['id'];
    
    // Fetch book references with user information
    $query = "
        SELECT 
            br.id,
            br.title,
            br.isbn,
            br.publisher,
            br.copyright_year,
            br.edition,
            br.location,
            br.call_number,
            br.created_by,
            br.requested_by,
            br.created_at,
            br.updated_at,
            CONCAT(u1.first_name, ' ', u1.last_name) as created_by_name,
            CONCAT(u2.first_name, ' ', u2.last_name) as requested_by_name
        FROM book_references br
        LEFT JOIN users u1 ON br.created_by = u1.id
        LEFT JOIN users u2 ON br.requested_by = u2.id
        WHERE br.course_id = ?
        ORDER BY br.created_at DESC
    ";
    
    $stmt = $pdo->prepare($query);
    $stmt->execute([$courseId]);
    $references = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo json_encode([
        'status' => 'success',
        'references' => $references,
        'count' => count($references)
    ]);
    
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'status' => 'error',
        'message' => 'Failed to fetch book references: ' . $e->getMessage()
    ]);
}

