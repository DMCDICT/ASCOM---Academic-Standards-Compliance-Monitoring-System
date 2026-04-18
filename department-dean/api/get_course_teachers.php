<?php
// get_course_teachers.php
// Get teachers assigned to a specific course

require_once '../includes/db_connection.php';

$course_id = $_GET['course_id'] ?? null;

header('Content-Type: application/json');

if (!$course_id) {
    echo json_encode(['success' => false, 'message' => 'Course ID is required']);
    exit;
}

try {
    $stmt = $pdo->prepare("
        SELECT 
            ca.id,
            ca.teacher_id,
            ca.assigned_at,
            u.employee_no,
            u.first_name,
            u.last_name,
            u.title,
            u.email
        FROM course_assignments ca
        JOIN users u ON ca.teacher_id = u.id
        WHERE ca.course_id = ? AND ca.is_active = TRUE
        ORDER BY u.first_name ASC
    ");
    $stmt->execute([$course_id]);
    $teachers = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo json_encode([
        'success' => true, 
        'teachers' => $teachers
    ]);
    
} catch (PDOException $e) {
    echo json_encode(['success' => false, 'message' => 'Error: ' . $e->getMessage()]);
}
?>
