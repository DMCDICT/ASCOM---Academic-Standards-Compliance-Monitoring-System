<?php
// save_course_draft.php
// API endpoint to save course draft

header('Content-Type: application/json');

require_once dirname(__FILE__) . '/../../session_config.php';
require_once dirname(__FILE__) . '/../includes/db_connection.php';

// Session should be configured by session_config.php, but ensure it's started
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// Debug: Log session info

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Invalid request method']);
    exit;
}

if (!isset($_SESSION['user_id'])) {
    http_response_code(401);
    echo json_encode([
        'success' => false, 
        'message' => 'User not authenticated',
        'debug' => [
            'session_id' => session_id(),
            'session_status' => session_status(),
            'has_user_id' => isset($_SESSION['user_id']),
            'session_keys' => array_keys($_SESSION ?? [])
        ]
    ]);
    exit;
}

try {
    $data = json_decode(file_get_contents('php://input'), true);
    
    if (!isset($data['courses']) || !is_array($data['courses']) || empty($data['courses'])) {
        echo json_encode(['success' => false, 'message' => 'No courses provided']);
        exit;
    }
    
    $userId = $_SESSION['user_id'];
    $programId = $data['program_id'] ?? null;
    $term = $data['term'] ?? null;
    $academicYear = $data['academic_year'] ?? null;
    $yearLevel = $data['year_level'] ?? null;
    
    // Start transaction
    $pdo->beginTransaction();
    
    // Delete existing drafts for this user
    $deleteStmt = $pdo->prepare("DELETE FROM course_drafts WHERE user_id = ?");
    $deleteStmt->execute([$userId]);
    
    // Save new draft
    $insertStmt = $pdo->prepare("
        INSERT INTO course_drafts (user_id, program_id, term, academic_year, year_level, courses_data, created_at, updated_at)
        VALUES (?, ?, ?, ?, ?, ?, NOW(), NOW())
    ");
    
    $coursesJson = json_encode($data['courses']);
    
    $insertStmt->execute([
        $userId,
        $programId,
        $term,
        $academicYear,
        $yearLevel,
        $coursesJson
    ]);
    
    $pdo->commit();
    
    echo json_encode([
        'success' => true,
        'message' => 'Draft saved successfully',
        'draft_id' => $pdo->lastInsertId()
    ]);
    
} catch (Exception $e) {
    if ($pdo->inTransaction()) {
        $pdo->rollBack();
    }
    
    echo json_encode([
        'success' => false,
        'message' => 'Error saving draft: ' . $e->getMessage()
    ]);
}
?>

