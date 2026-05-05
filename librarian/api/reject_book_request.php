<?php
/**
 * reject_book_request.php
 * API for librarian to reject a book request from dean
 */

header('Content-Type: application/json');
require_once dirname(__DIR__, 2) . '/bootstrap/database.php';
require_once dirname(__DIR__, 2) . '/session_config.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$response = ['success' => false, 'message' => '', 'data' => null];

// Check if user is logged in and is a librarian
if (!isset($_SESSION['user_id'])) {
    $response['message'] = 'Authentication required. Please log in.';
    echo json_encode($response);
    exit;
}

$userRoleType = strtolower((string) ($_SESSION['user_role'] ?? ''));
$selectedRole = is_array($_SESSION['selected_role'] ?? null) ? $_SESSION['selected_role'] : [];
$selectedRoleType = strtolower((string) ($selectedRole['type'] ?? ($selectedRole['role_name'] ?? '')));

if (empty($_SESSION['librarian_logged_in']) && $userRoleType !== 'librarian' && $selectedRoleType !== 'librarian') {
    $response['message'] = 'Access denied. Only librarians can reject book requests.';
    echo json_encode($response);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        // Get input data
        $input = json_decode(file_get_contents('php://input'), true);
        if (!$input) {
            $input = $_POST;
        }
        
        $requestId = $input['request_id'] ?? null;
        $rejectionReason = trim($input['rejection_reason'] ?? '');
        
        // Validate required fields
        if (!$requestId) {
            $response['message'] = 'Request ID is required.';
            echo json_encode($response);
            exit;
        }
        
        if (empty($rejectionReason)) {
            $response['message'] = 'Please provide a reason for rejecting this request.';
            echo json_encode($response);
            exit;
        }
        
        // Check if book_requests table exists
        $tableCheck = $pdo->query("SHOW TABLES LIKE 'book_requests'");
        if ($tableCheck->rowCount() === 0) {
            $response['message'] = 'Book requests table does not exist.';
            echo json_encode($response);
            exit;
        }
        
        // Get the book request
        $requestQuery = "SELECT * FROM book_requests WHERE id = ? AND status = 'PENDING'";
        $requestStmt = $pdo->prepare($requestQuery);
        $requestStmt->execute([$requestId]);
        $bookRequest = $requestStmt->fetch(PDO::FETCH_ASSOC);
        
        if (!$bookRequest) {
            $response['message'] = 'Book request not found or already processed.';
            echo json_encode($response);
            exit;
        }
        
        // Update book_requests status to REJECTED
        $updateRequest = "UPDATE book_requests 
            SET status = 'REJECTED', rejection_reason = ?, processed_by = ?, processed_at = NOW() 
            WHERE id = ?";
        
        $updateStmt = $pdo->prepare($updateRequest);
        $updateStmt->execute([$rejectionReason, $_SESSION['user_id'], $requestId]);
        
        $response['success'] = true;
        $response['message'] = 'Book request rejected.';
        $response['data'] = [
            'request_id' => $requestId,
            'book_title' => $bookRequest['book_title'],
            'status' => 'REJECTED',
            'rejection_reason' => $rejectionReason
        ];
        
    } catch (Exception $e) {
        $response['message'] = 'Failed to reject book request: ' . $e->getMessage();
    }
} else {
    $response['message'] = 'Invalid request method. Use POST.';
}

echo json_encode($response);
