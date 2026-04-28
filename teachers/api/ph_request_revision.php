<?php
// ph_request_revision.php - Program head requests revision for a syllabus
require_once dirname(__FILE__, 3) . '/session_config.php';
require_once dirname(__FILE__, 3) . '/bootstrap/auth.php';

ascom_require_role('teacher', dirname(__FILE__, 3) . '/../user_login.php');

header('Content-Type: application/json');

try {
    require_once dirname(__FILE__, 3) . '/../super_admin-mis/includes/db_connection.php';
    
    $input = json_decode(file_get_contents('php://input'), true);
    $syllabus_id = $input['syllabus_id'] ?? null;
    $note = $input['note'] ?? null;
    $reviewer_id = $_SESSION['user_id'];
    
    if (!$syllabus_id) {
        echo json_encode(['success' => false, 'message' => 'Syllabus ID is required']);
        exit;
    }
    
    // Verify the user is the program head for this syllabus's program
    $verifyStmt = $pdo->prepare("
        SELECT ph.id
        FROM program_heads ph
        JOIN course_syllabi cs ON cs.course_id IN (
            SELECT id FROM courses WHERE program_id = ph.program_id
        )
        WHERE ph.teacher_id = ? AND ph.is_active = TRUE AND cs.id = ?
    ");
    $verifyStmt->execute([$reviewer_id, $syllabus_id]);
    
    if (!$verifyStmt->fetch()) {
        echo json_encode(['success' => false, 'message' => 'You are not the program head for this course']);
        exit;
    }
    
    // Update syllabus status
    $updateStmt = $pdo->prepare("
        UPDATE course_syllabi SET
            status = 'revision_requested',
            ph_review_comments = ?,
            revision_count = revision_count + 1,
            last_revision_note = ?,
            updated_at = NOW()
        WHERE id = ?
    ");
    $updateStmt->execute([$note, $note, $syllabus_id]);
    
    // Log activity
    $username = $_SESSION['username'] ?? 'unknown';
    $logStmt = $pdo->prepare("
        INSERT INTO activity_logs (user_id, username, activity_type, description, target_entity, target_entity_id)
        VALUES (?, ?, 'syllabus_ph_revision', 'Program head requested syllabus revision', 'course_syllabi', ?)
    ");
    $logStmt->execute([$reviewer_id, $username, $syllabus_id]);
    
    echo json_encode(['success' => true, 'message' => 'Revision requested successfully']);
    
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => 'Error: ' . $e->getMessage()]);
}
?>