<?php
/**
 * cancel_book_request.php
 * API for dean to cancel their pending book request
 */

header('Content-Type: application/json');
require_once dirname(__FILE__) . '/../../session_config.php';
require_once dirname(__FILE__) . '/../includes/db_connection.php';

// session_config.php configures cookies but doesn't always start the session.
// API endpoints must start it to access $_SESSION.
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$response = ['success' => false, 'message' => '', 'data' => null];

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    $response['message'] = 'Authentication required. Please log in.';
    echo json_encode($response);
    exit;
}

// Check if user has dean role
$userRoleType = strtolower((string) ($_SESSION['user_role'] ?? ''));
$selectedRole = is_array($_SESSION['selected_role'] ?? null) ? $_SESSION['selected_role'] : [];
$selectedRoleType = strtolower((string) ($selectedRole['type'] ?? ($selectedRole['role_name'] ?? '')));

if (empty($_SESSION['dean_logged_in']) && $userRoleType !== 'dean' && $selectedRoleType !== 'dean') {
    $response['message'] = 'Access denied. Only deans can cancel book requests.';
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
        
        // Validate required fields
        if (!$requestId) {
            $response['message'] = 'Request ID is required.';
            echo json_encode($response);
            exit;
        }
        
        // Check if request exists and belongs to this dean and is pending
        $checkQuery = "SELECT * FROM book_requests WHERE id = ? AND requesting_dean_id = ? AND status = 'PENDING'";
        $checkStmt = $pdo->prepare($checkQuery);
        $checkStmt->execute([$requestId, $_SESSION['user_id']]);
        $existingRequest = $checkStmt->fetch(PDO::FETCH_ASSOC);
        
        if (!$existingRequest) {
            $response['message'] = 'Request not found or cannot be cancelled (either not yours or already processed).';
            echo json_encode($response);
            exit;
        }
        
        // Delete the request (soft delete by changing status would be better but this works)
        $deleteQuery = "DELETE FROM book_requests WHERE id = ? AND requesting_dean_id = ? AND status = 'PENDING'";
        $deleteStmt = $pdo->prepare($deleteQuery);
        $deleteStmt->execute([$requestId, $_SESSION['user_id']]);

        $response['success'] = true;
        $response['message'] = 'Book request cancelled successfully.';

    } catch (Exception $e) {
        $response['message'] = 'Failed to cancel book request: ' . $e->getMessage();
    }
} else {
    $response['message'] = 'Invalid request method. Use POST.';
}

echo json_encode($response);
