<?php
// update_processing_status.php
// API endpoint to update the processing status of book references

header('Content-Type: application/json');
require_once dirname(__FILE__) . '/../includes/db_connection.php';

$response = ['success' => false, 'message' => ''];

// Check if request is POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    $response['message'] = 'Invalid request method';
    echo json_encode($response);
    exit;
}

try {
    $input = json_decode(file_get_contents('php://input'), true);
    
    $bookId = $input['book_id'] ?? null;
    $status = $input['status'] ?? null; // 'completed', 'drafted', or 'processing'
    $callNumber = $input['call_number'] ?? null;
    $noOfCopies = $input['no_of_copies'] ?? null;
    $statusReason = $input['status_reason'] ?? null;
    $location = $input['location'] ?? null;
    
    // Validate required fields
    if (!$bookId) {
        throw new Exception('Book ID is required');
    }
    
    if (!$status || !in_array($status, ['completed', 'drafted', 'processing'])) {
        throw new Exception('Valid status is required');
    }
    
    // If completing, call_number and location are required
    if ($status === 'completed' && (empty($callNumber) || empty($location))) {
        throw new Exception('Call number and location are required for completion');
    }
    
    // If drafting, status_reason is required
    if ($status === 'drafted' && empty($statusReason)) {
        throw new Exception('Status reason is required for drafting');
    }
    
    // Update the book reference
    if ($status === 'completed') {
        $updateQuery = "
            UPDATE book_references 
            SET processing_status = 'completed',
                call_number = ?,
                no_of_copies = COALESCE(?, 1),
                location = ?,
                updated_at = CURRENT_TIMESTAMP
            WHERE id = ?
        ";
        $stmt = $pdo->prepare($updateQuery);
        $stmt->execute([$callNumber, $noOfCopies, $location, $bookId]);
    } else if ($status === 'processing') {
        // Resume processing (move from drafted back to processing)
        $updateQuery = "
            UPDATE book_references 
            SET processing_status = 'processing',
                status_reason = NULL,
                updated_at = CURRENT_TIMESTAMP
            WHERE id = ?
        ";
        $stmt = $pdo->prepare($updateQuery);
        $stmt->execute([$bookId]);
    } else {
        // Drafted
        $updateQuery = "
            UPDATE book_references 
            SET processing_status = 'drafted',
                status_reason = ?,
                updated_at = CURRENT_TIMESTAMP
            WHERE id = ?
        ";
        $stmt = $pdo->prepare($updateQuery);
        $stmt->execute([$statusReason, $bookId]);
    }
    
    $response['success'] = true;
    $response['message'] = $status === 'completed' ? 'Book reference completed successfully' : ($status === 'drafted' ? 'Book reference drafted successfully' : 'Processing resumed successfully');
    
} catch (Exception $e) {
    error_log("Error updating processing status: " . $e->getMessage());
    $response['message'] = 'Failed to update status: ' . $e->getMessage();
}

echo json_encode($response);
exit;

