<?php
/**
 * approve_book_request.php
 * API for librarian to approve a book request and add it to the library catalog
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
    $response['message'] = 'Access denied. Only librarians can approve book requests.';
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
        
        // Check if book_requests table exists, if not create it
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
        
        // Get additional library catalog fields from input
        $callNumber = trim($input['call_number'] ?? '');
        $location = trim($input['location'] ?? '');
        $noOfCopies = (int)($input['no_of_copies'] ?? 1);
        $subjectCategory = trim($input['subject_category'] ?? '');
        $description = trim($input['description'] ?? '');
        $keywords = trim($input['keywords'] ?? '');
        
        // Validate required library fields
        if (empty($callNumber)) {
            $response['message'] = 'Call number is required to add book to library.';
            echo json_encode($response);
            exit;
        }
        
        // Check if call number already exists
        $callNumberCheck = "SELECT id FROM library_books WHERE call_number = ?";
        $callNumberStmt = $pdo->prepare($callNumberCheck);
        $callNumberStmt->execute([$callNumber]);
        if ($callNumberStmt->fetch()) {
            $response['message'] = 'A book with this call number already exists in the library.';
            echo json_encode($response);
            exit;
        }
        
        // Insert into library_books
        $insertLibraryBook = "INSERT INTO library_books 
            (call_number, isbn, title, authors, publisher, copyright_year, edition, no_of_copies, available_copies, location, subject_category, description, keywords, created_by) 
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
        
        $libraryStmt = $pdo->prepare($insertLibraryBook);
        $libraryStmt->execute([
            $callNumber,
            $bookRequest['isbn'] ?: null,
            $bookRequest['book_title'],
            $bookRequest['author'],
            $bookRequest['publisher'] ?: null,
            $bookRequest['publication_year'] ?: null,
            $bookRequest['edition'] ?: null,
            $noOfCopies,
            $noOfCopies,
            $location ?: null,
            $subjectCategory ?: null,
            $description ?: null,
            $keywords ?: null,
            $_SESSION['user_id']
        ]);
        
        $libraryBookId = $pdo->lastInsertId();
        
        // Update book_requests status to APPROVED
        $updateRequest = "UPDATE book_requests 
            SET status = 'APPROVED', processed_by = ?, processed_at = NOW() 
            WHERE id = ?";
        
        $updateStmt = $pdo->prepare($updateRequest);
        $updateStmt->execute([$_SESSION['user_id'], $requestId]);
        
        $response['success'] = true;
        $response['message'] = 'Book request approved. Book added to library catalog.';
        $response['data'] = [
            'request_id' => $requestId,
            'library_book_id' => $libraryBookId,
            'book_title' => $bookRequest['book_title'],
            'call_number' => $callNumber,
            'status' => 'APPROVED'
        ];
        
    } catch (Exception $e) {
        $response['message'] = 'Failed to approve book request: ' . $e->getMessage();
    }
} else {
    $response['message'] = 'Invalid request method. Use POST.';
}

echo json_encode($response);
