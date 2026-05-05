<?php
/**
 * update_book_request.php
 * API for dean to edit their pending book request
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
    $response['message'] = 'Access denied. Only deans can edit book requests.';
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
            $response['message'] = 'Request not found or cannot be edited (either not yours or already processed).';
            echo json_encode($response);
            exit;
        }
        
        // Validate required fields
        $bookTitle = trim($input['book_title'] ?? '');
        $author = trim($input['author'] ?? '');
        $reason = trim($input['reason'] ?? '');

        if (empty($bookTitle)) {
            $response['message'] = 'Book title is required.';
            echo json_encode($response);
            exit;
        }

        if (empty($author)) {
            $response['message'] = 'Author is required.';
            echo json_encode($response);
            exit;
        }

        if (empty($reason)) {
            $response['message'] = 'Please provide a reason for this request.';
            echo json_encode($response);
            exit;
        }

        // Prepare optional fields
        $isbn = trim($input['isbn'] ?? '');
        $publisher = trim($input['publisher'] ?? '');
        $publicationYear = trim($input['publication_year'] ?? '');
        $edition = trim($input['edition'] ?? '');

        // Update the request
        $query = "UPDATE book_requests 
                  SET book_title = ?, author = ?, isbn = ?, publisher = ?, publication_year = ?, edition = ?, reason = ?
                  WHERE id = ? AND status = 'PENDING'";
        
        $stmt = $pdo->prepare($query);
        $stmt->execute([
            $bookTitle,
            $author,
            $isbn ?: null,
            $publisher ?: null,
            $publicationYear ?: null,
            $edition ?: null,
            $reason,
            $requestId
        ]);

        $response['success'] = true;
        $response['message'] = 'Book request updated successfully.';
        $response['data'] = [
            'request_id' => $requestId,
            'book_title' => $bookTitle,
            'status' => 'PENDING'
        ];

    } catch (Exception $e) {
        $response['message'] = 'Failed to update book request: ' . $e->getMessage();
    }
} else {
    $response['message'] = 'Invalid request method. Use POST.';
}

echo json_encode($response);
