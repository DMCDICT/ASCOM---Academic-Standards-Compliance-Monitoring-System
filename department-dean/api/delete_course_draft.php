<?php
require_once dirname(__FILE__) . '/../../session_config.php';
session_start();
header('Content-Type: application/json');

require_once dirname(__FILE__) . '/../includes/db_connection.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Invalid request method']);
    exit;
}

// Check authentication
if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'User not authenticated']);
    exit;
}

try {
    $data = json_decode(file_get_contents('php://input'), true);
    
    $userId = $_SESSION['user_id'];
    $proposalId = $data['proposal_id'] ?? null;
    $programId = $data['program_id'] ?? null;
    
    
    if (!$proposalId) {
        echo json_encode([
            'success' => false,
            'message' => 'Proposal ID is required'
        ]);
        exit;
    }
    
    // Delete the draft by ID (the proposal_id should match the draft id in course_drafts table)
    // First, let's check if the draft exists and belongs to the user
    $checkStmt = $pdo->prepare("SELECT id FROM course_drafts WHERE id = ? AND user_id = ?");
    $checkStmt->execute([$proposalId, $userId]);
    $draft = $checkStmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$draft) {
        // Try to find by matching the draft ID pattern (draft_ prefix)
        // The proposal_id might be like "draft_3" but the actual ID in DB is just "3"
        $draftId = str_replace('draft_', '', $proposalId);
        $checkStmt2 = $pdo->prepare("SELECT id FROM course_drafts WHERE id = ? AND user_id = ?");
        $checkStmt2->execute([$draftId, $userId]);
        $draft = $checkStmt2->fetch(PDO::FETCH_ASSOC);
        
        if ($draft) {
            $actualDraftId = $draft['id'];
        } else {
            echo json_encode([
                'success' => false,
                'message' => 'Draft not found or you do not have permission to delete it'
            ]);
            exit;
        }
    } else {
        $actualDraftId = $draft['id'];
    }
    
    // Delete the draft
    $deleteStmt = $pdo->prepare("DELETE FROM course_drafts WHERE id = ? AND user_id = ?");
    $deleteStmt->execute([$actualDraftId, $userId]);
    
    if ($deleteStmt->rowCount() > 0) {
        echo json_encode([
            'success' => true,
            'message' => 'Draft deleted successfully'
        ]);
    } else {
        echo json_encode([
            'success' => false,
            'message' => 'Draft not found or already deleted'
        ]);
    }
    
} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'message' => 'Error deleting draft: ' . $e->getMessage()
    ]);
}
?>

