<?php
// API endpoint to get overview stats filtered by term
header('Content-Type: application/json');

// Include database connection
require_once dirname(__FILE__) . '/../includes/db_connection.php';

// Start session
require_once dirname(__FILE__) . '/../../session_config.php';
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

try {
    // Get teacher ID from session
    if (!isset($_SESSION['user_id'])) {
        throw new Exception('User not authenticated.');
    }
    
    $teacherId = $_SESSION['user_id'];
    $termId = $_GET['term_id'] ?? null;
    $termName = null;
    
    // If term_id is provided, get the term name
    if ($termId) {
        // Check which table exists
        $tableCheck = $pdo->query("SHOW TABLES LIKE 'school_terms'");
        $hasSchoolTerms = $tableCheck->rowCount() > 0;
        
        if ($hasSchoolTerms) {
            $termQuery = "SELECT title FROM school_terms WHERE id = ?";
        } else {
            // Try 'terms' table
            $termQuery = "SELECT name as title FROM terms WHERE id = ?";
        }
        
        $termStmt = $pdo->prepare($termQuery);
        $termStmt->execute([$termId]);
        $termData = $termStmt->fetch(PDO::FETCH_ASSOC);
        $termName = $termData['title'] ?? null;
    }
    
    // Build query to get teacher's courses
    $baseQuery = "
        SELECT 
            c.id,
            c.course_code,
            c.course_title,
            c.term,
            c.status,
            CASE 
                WHEN EXISTS (
                    SELECT 1 FROM book_references br 
                    WHERE br.course_id = c.id 
                    AND br.status = 'approved'
                ) THEN 'Compliant'
                ELSE 'Non-Compliant'
            END as compliance_status
        FROM courses c
        WHERE c.faculty_id = ?
    ";
    
    $params = [$teacherId];
    
    // Add term filter if term is selected
    if ($termName) {
        $baseQuery .= " AND c.term = ?";
        $params[] = $termName;
    }
    
    $stmt = $pdo->prepare($baseQuery);
    $stmt->execute($params);
    $courses = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Calculate stats
    $totalCourses = count($courses);
    $compliantCourses = 0;
    $nonCompliantCourses = 0;
    
    foreach ($courses as $course) {
        if ($course['compliance_status'] === 'Compliant') {
            $compliantCourses++;
        } else {
            $nonCompliantCourses++;
        }
    }
    
    echo json_encode([
        'status' => 'success',
        'stats' => [
            'total_courses' => $totalCourses,
            'compliant_courses' => $compliantCourses,
            'non_compliant_courses' => $nonCompliantCourses
        ],
        'term_name' => $termName
    ]);
    
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'status' => 'error',
        'message' => 'Failed to fetch overview stats: ' . $e->getMessage()
    ]);
}

