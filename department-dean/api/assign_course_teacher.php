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
    
    // Get the current user's ID for assigned_by (from session)
    $assigned_by = $_SESSION['user_id'] ?? $teacher_id;
    
    // Start transaction
    $pdo->beginTransaction();
    
    // Deactivate existing active assignments for this course
    $deactivate = $pdo->prepare("
        UPDATE course_assignments 
        SET is_active = FALSE 
        WHERE course_id = ?
    ");
    $deactivate->execute([$course_id]);
    
    // Insert new assignment
    $insert = $pdo->prepare("
        INSERT INTO course_assignments (course_id, teacher_id, assigned_by) 
        VALUES (?, ?, ?)
    ");
    $insert->execute([$course_id, $teacher_id, $assigned_by]);
    
    // Also update the faculty_id in the courses table for backward compatibility
    $updateCourse = $pdo->prepare("
        UPDATE courses SET faculty_id = ? WHERE id = ?
    ");
    $updateCourse->execute([$teacher_id, $course_id]);
    
    // Commit transaction
    $pdo->commit();
    
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
