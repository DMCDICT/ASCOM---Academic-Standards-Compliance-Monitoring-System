<?php
// dean_request_revision.php - Dean requests revision for a syllabus
require_once dirname(__FILE__) . '/../../session_config.php';
require_once dirname(__FILE__) . '/../../bootstrap/auth.php';

ascom_require_role('dean', dirname(__FILE__) . '/../../user_login.php');

header('Content-Type: application/json');

try {
    require_once dirname(__FILE__) . '/../../super_admin-mis/includes/db_connection.php';
    
    $input = json_decode(file_get_contents('php://input'), true);
    $syllabus_id = $input['syllabus_id'] ?? null;
    $note = $input['note'] ?? null;
    $reviewer_id = $_SESSION['user_id'];
    
    if (!$syllabus_id) {
        echo json_encode(['success' => false, 'message' => 'Syllabus ID is required']);
        exit;
    }
    
    // Verify the user is the dean
    $syllabusStmt = $pdo->prepare("
        SELECT cs.id, cs.course_id, c.department_id
        FROM course_syllabi cs
        JOIN courses c ON cs.course_id = c.id
        WHERE cs.id = ?
    ");
    $syllabusStmt->execute([$syllabus_id]);
    $syllabus = $syllabusStmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$syllabus) {
        echo json_encode(['success' => false, 'message' => 'Syllabus not found']);
        exit;
    }
    
    // Verify dean
    $deanStmt = $pdo->prepare("SELECT id FROM departments WHERE id = ? AND dean_user_id = ?");
    $deanStmt->execute([$syllabus['department_id'], $reviewer_id]);
    
    if (!$deanStmt->fetch()) {
        echo json_encode(['success' => false, 'message' => 'You are not the dean for this department']);
        exit;
    }
    
    // Update syllabus status
    $updateStmt = $pdo->prepare("
        UPDATE course_syllabi SET
            status = 'revision_requested',
            dean_reviewer_id = ?,
            dean_review_comments = ?,
            revision_count = revision_count + 1,
            last_revision_note = ?,
            updated_at = NOW()
        WHERE id = ?
    ");
    $updateStmt->execute([$reviewer_id, $note, $note, $syllabus_id]);
    
    // Update course syllabus status
    $courseStmt = $pdo->prepare("UPDATE courses SET syllabus_status = 'needs_revision' WHERE id = ?");
    $courseStmt->execute([$syllabus['course_id']]);
    
    // Log activity
    $username = $_SESSION['username'] ?? 'unknown';
    $logStmt = $pdo->prepare("
        INSERT INTO activity_logs (user_id, username, activity_type, description, target_entity, target_entity_id)
        VALUES (?, ?, 'syllabus_revision_requested', 'Dean requested syllabus revision', 'course_syllabi', ?)
    ");
    $logStmt->execute([$reviewer_id, $username, $syllabus_id]);
    
    echo json_encode(['success' => true, 'message' => 'Revision requested successfully']);
    
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => 'Error: ' . $e->getMessage()]);
}
?>