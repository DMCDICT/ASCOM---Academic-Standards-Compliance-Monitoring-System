<?php
// reject_curriculum.php
// API endpoint to reject a curriculum proposal

header('Content-Type: application/json');

require_once dirname(__FILE__) . '/../includes/db_connection.php';

// Start session if not already started
if (session_status() == PHP_SESSION_NONE) {
    session_name('ASCOM_SESSION');
    session_start();
}

try {
    // Check if user is authenticated as QA
    $isAuthenticated = false;
    
    if (isset($_SESSION['qa_logged_in']) && $_SESSION['qa_logged_in'] === true) {
        $isAuthenticated = true;
    }
    elseif (isset($_SESSION['selected_role']) && $_SESSION['selected_role']['type'] === 'quality_assurance') {
        $isAuthenticated = true;
        $_SESSION['qa_logged_in'] = true;
    }
    elseif (isset($_SESSION['user_id']) && isset($_SESSION['username'])) {
        $isAuthenticated = true;
    }
    
    if (!$isAuthenticated) {
        http_response_code(401);
        echo json_encode([
            'success' => false,
            'message' => 'Unauthorized. Please log in as Quality Assurance.'
        ]);
        exit;
    }
    
    // Get JSON input
    $input = file_get_contents('php://input');
    $data = json_decode($input, true);
    
    if (!isset($data['proposal_id']) || empty($data['proposal_id'])) {
        http_response_code(400);
        echo json_encode([
            'success' => false,
            'message' => 'Proposal ID is required'
        ]);
        exit;
    }
    
    $proposalId = (int)$data['proposal_id'];
    $rejectionReason = $data['reason'] ?? $data['rejection_reason'] ?? '';
    $qaUserId = $_SESSION['user_id'] ?? null;
    
    // Validate rejection reason
    if (empty($rejectionReason)) {
        http_response_code(400);
        echo json_encode([
            'success' => false,
            'message' => 'Rejection reason is required'
        ]);
        exit;
    }
    
    // Start transaction
    $pdo->beginTransaction();
    
    try {
        // Check if proposal exists and is in pending status
        $checkStmt = $pdo->prepare("
            SELECT id, status, courses_data 
            FROM course_proposals 
            WHERE id = ?
        ");
        $checkStmt->execute([$proposalId]);
        $proposal = $checkStmt->fetch(PDO::FETCH_ASSOC);
        
        if (!$proposal) {
            throw new Exception('Proposal not found');
        }
        
        if ($proposal['status'] !== 'Pending QA Review' && $proposal['status'] !== 'PENDING' && $proposal['status'] !== 'Draft') {
            throw new Exception('Proposal cannot be rejected. Current status: ' . $proposal['status']);
        }
        
        // Update proposal status to REJECTED
        $updateStmt = $pdo->prepare("
            UPDATE course_proposals 
            SET status = 'Rejected',
                reviewed_at = NOW(),
                reviewed_by = ?,
                review_notes = ?
            WHERE id = ?
        ");
        $updateStmt->execute([$qaUserId, $rejectionReason, $proposalId]);
        
        // Commit transaction
        $pdo->commit();
        
        echo json_encode([
            'success' => true,
            'message' => 'Curriculum proposal rejected',
            'proposal_id' => $proposalId,
            'rejection_reason' => $rejectionReason
        ]);
        
    } catch (Exception $e) {
        $pdo->rollBack();
        throw $e;
    }
    
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Failed to reject curriculum proposal',
        'error' => $e->getMessage()
    ]);
}
?>