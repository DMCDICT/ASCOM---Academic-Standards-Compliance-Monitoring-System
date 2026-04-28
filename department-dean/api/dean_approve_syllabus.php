<?php
// dean_approve_syllabus.php - Dean gives final approval to a syllabus
require_once dirname(__FILE__) . '/../../session_config.php';
require_once dirname(__FILE__) . '/../../bootstrap/auth.php';

ascom_require_role('dean', dirname(__FILE__) . '/../../user_login.php');

header('Content-Type: application/json');

try {
    require_once dirname(__FILE__) . '/../../super_admin-mis/includes/db_connection.php';
    
    $input = json_decode(file_get_contents('php://input'), true);
    $syllabus_id = $input['syllabus_id'] ?? null;
    $reviewer_id = $_SESSION['user_id'];
    $comments = $input['comments'] ?? null;
    
    if (!$syllabus_id) {
        echo json_encode(['success' => false, 'message' => 'Syllabus ID is required']);
        exit;
    }
    
    // Verify the syllabus is ready for dean approval (status = ph_approved)
    $verifyStmt = $pdo->prepare("
        SELECT cs.id, cs.course_id, c.department_id, c.program_id
        FROM course_syllabi cs
        JOIN courses c ON cs.course_id = c.id
        WHERE cs.id = ? AND cs.status = 'ph_approved'
    ");
    $verifyStmt->execute([$syllabus_id]);
    $syllabus = $verifyStmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$syllabus) {
        echo json_encode(['success' => false, 'message' => 'Syllabus is not ready for dean approval']);
        exit;
    }
    
    // Verify the user is the dean of this department
    $deanStmt = $pdo->prepare("
        SELECT id FROM departments WHERE id = ? AND dean_user_id = ?
    ");
    $deanStmt->execute([$syllabus['department_id'], $reviewer_id]);
    
    if (!$deanStmt->fetch()) {
        echo json_encode(['success' => false, 'message' => 'You are not the dean for this department']);
        exit;
    }
    
    // Update syllabus status
    $updateStmt = $pdo->prepare("
        UPDATE course_syllabi SET
            status = 'dean_approved',
            dean_reviewer_id = ?,
            dean_review_comments = ?,
            dean_approved_at = NOW(),
            updated_at = NOW()
        WHERE id = ?
    ");
    $updateStmt->execute([$reviewer_id, $comments, $syllabus_id]);
    
    // Update course syllabus status
    $courseStmt = $pdo->prepare("
        UPDATE courses SET syllabus_status = 'approved', last_syllabus_update = NOW() WHERE id = ?
    ");
    $courseStmt->execute([$syllabus['course_id']]);
    
    // Update course_assignments
    $assignmentStmt = $pdo->prepare("
        UPDATE course_assignments 
        SET syllabus_status = 'dean_approved', dean_approved_at = NOW() 
        WHERE course_id = ?
    ");
    $assignmentStmt->execute([$syllabus['course_id']]);
    
    // Log activity
    $username = $_SESSION['username'] ?? 'unknown';
    $logStmt = $pdo->prepare("
        INSERT INTO activity_logs (user_id, username, activity_type, description, target_entity, target_entity_id)
        VALUES (?, ?, 'syllabus_dean_approve', 'Syllabus approved by dean', 'course_syllabi', ?)
    ");
    $logStmt->execute([$reviewer_id, $username, $syllabus_id]);
    
    echo json_encode(['success' => true, 'message' => 'Syllabus approved successfully']);
    
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => 'Error: ' . $e->getMessage()]);
}
?>