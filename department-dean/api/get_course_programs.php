<?php
// API endpoint to get programs for a specific course
require_once '../includes/db_connection.php';

header('Content-Type: application/json');

try {
    $courseCode = $_GET['course_code'] ?? '';
    
    if (empty($courseCode)) {
        echo json_encode(['error' => 'Course code is required']);
        exit;
    }
    
    // Try to get from course_programs table first
    $query = "
        SELECT p.id, p.program_code, p.program_name, p.color_code
        FROM course_programs cp
        JOIN programs p ON cp.program_id = p.id
        WHERE cp.course_code = ?
        ORDER BY p.program_code ASC
    ";
    
    $stmt = $pdo->prepare($query);
    $stmt->execute([$courseCode]);
    $programs = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // If no programs found in course_programs, try the old method
    if (empty($programs)) {
        $query = "
            SELECT p.id, p.program_code, p.program_name, p.color_code
            FROM courses c
            LEFT JOIN programs p ON c.program_id = p.id
            WHERE c.course_code = ?
        ";
        
        $stmt = $pdo->prepare($query);
        $stmt->execute([$courseCode]);
        $programs = $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    echo json_encode($programs);
    
} catch (Exception $e) {
    echo json_encode(['error' => $e->getMessage()]);
}
?>
