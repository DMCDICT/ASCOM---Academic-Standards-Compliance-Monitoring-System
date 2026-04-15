<?php
// assign_course_teacher.php
// Assign a teacher to a course

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
    // Check if teacher is already assigned to this course
    $checkExisting = $pdo->prepare("
        SELECT id FROM course_assignments 
        WHERE course_id = ? AND teacher_id = ? AND is_active = TRUE
    ");
    $checkExisting->execute([$course_id, $teacher_id]);
    
    if ($checkExisting->fetch()) {
        echo json_encode([
            'success' => false, 
            'message' => 'This teacher is already assigned to this course'
        ]);
        exit;
    }
    
    // Insert new assignment
    $assigned_by = $teacher_id; // In real implementation, get from session
    $insert = $pdo->prepare("
        INSERT INTO course_assignments (course_id, teacher_id, assigned_by) 
        VALUES (?, ?, ?)
    ");
    $insert->execute([$course_id, $teacher_id, $assigned_by]);
    
    echo json_encode([
        'success' => true, 
        'message' => 'Teacher assigned to course successfully'
    ]);
    
} catch (PDOException $e) {
    if ($e->getCode() == 23000) {
        echo json_encode([
            'success' => false, 
            'message' => 'This teacher is already assigned to this course'
        ]);
    } else {
        echo json_encode(['success' => false, 'message' => 'Error: ' . $e->getMessage()]);
    }
}
?>
