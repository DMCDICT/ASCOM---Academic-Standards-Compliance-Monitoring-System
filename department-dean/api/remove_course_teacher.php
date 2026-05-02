<?php
// remove_course_teacher.php
// Remove a teacher from a course

require_once '../includes/db_connection.php';

$input = json_decode(file_get_contents('php://input'), true);

$course_id = $input['course_id'] ?? null;
$teacher_id = $input['teacher_id'] ?? null;

header('Content-Type: application/json');

if (!$course_id || !$teacher_id) {
    echo json_encode(['success' => false, 'message' => 'Course and teacher are required']);
    exit;
}

try {
    // Start transaction
    $pdo->beginTransaction();
    
    // Deactivate the assignment
    $deactivate = $pdo->prepare("
        UPDATE course_assignments 
        SET is_active = FALSE 
        WHERE course_id = ? AND teacher_id = ?
    ");
    $deactivate->execute([$course_id, $teacher_id]);
    
    // Also clear the faculty_id in the courses table if this was the active teacher
    $updateCourse = $pdo->prepare("
        UPDATE courses SET faculty_id = NULL WHERE id = ? AND faculty_id = ?
    ");
    $updateCourse->execute([$course_id, $teacher_id]);
    
    // Commit transaction
    $pdo->commit();
    
    echo json_encode(['success' => true, 'message' => 'Teacher removed from course successfully']);
    
} catch (PDOException $e) {
    echo json_encode(['success' => false, 'message' => 'Error: ' . $e->getMessage()]);
}
?>
