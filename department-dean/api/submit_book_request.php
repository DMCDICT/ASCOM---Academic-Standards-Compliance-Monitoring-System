<?php
/**
 * submit_book_request.php
 * API for dean to submit a book request to the library
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
    $response['message'] = 'Access denied. Only deans can submit book requests.';
    echo json_encode($response);
    exit;
}

// Get department code from session
$departmentCode = $selectedRole['department_code'] ?? null;
if (!$departmentCode) {
    $response['message'] = 'Department not identified. Please select a department.';
    echo json_encode($response);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        // Auto-create table if it doesn't exist
        $tableCheck = $pdo->query("SHOW TABLES LIKE 'book_requests'");
        if ($tableCheck->rowCount() == 0) {
            $createTableSQL = "CREATE TABLE IF NOT EXISTS `book_requests` (
                `id` INT(11) NOT NULL AUTO_INCREMENT,
                `requesting_dean_id` INT(11) NOT NULL,
                `department_code` VARCHAR(50) NOT NULL,
                `book_title` VARCHAR(255) NOT NULL,
                `author` VARCHAR(255) NULL,
                `isbn` VARCHAR(30) NULL,
                `publisher` VARCHAR(150) NULL,
                `publication_year` VARCHAR(10) NULL,
                `edition` VARCHAR(50) NULL,
                `reason` TEXT NULL,
                `status` ENUM('PENDING', 'DONE') NOT NULL DEFAULT 'PENDING',
                `requested_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
                `processed_by` INT(11) NULL,
                `processed_at` TIMESTAMP NULL,
                PRIMARY KEY (`id`),
                KEY `idx_requesting_dean_id` (`requesting_dean_id`),
                KEY `idx_department_code` (`department_code`),
                KEY `idx_status` (`status`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci";
            $pdo->exec($createTableSQL);
        }
        
        // Get input data
        $input = json_decode(file_get_contents('php://input'), true);
        if (!$input) {
            $input = $_POST;
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
            $response['message'] = 'Please provide a reason for this request (e.g., which course needs this for compliance).';
            echo json_encode($response);
            exit;
        }

        // Prepare optional fields
        $isbn = trim($input['isbn'] ?? '');
        $publisher = trim($input['publisher'] ?? '');
        $publicationYear = trim($input['publication_year'] ?? '');
        $edition = trim($input['edition'] ?? '');

        // Insert the request
        $query = "INSERT INTO book_requests 
                  (requesting_dean_id, department_code, book_title, author, isbn, publisher, publication_year, edition, reason, status) 
                  VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, 'PENDING')";
        
        $stmt = $pdo->prepare($query);
        $stmt->execute([
            $_SESSION['user_id'],
            $departmentCode,
            $bookTitle,
            $author,
            $isbn ?: null,
            $publisher ?: null,
            $publicationYear ?: null,
            $edition ?: null,
            $reason
        ]);

        $requestId = $pdo->lastInsertId();

        $response['success'] = true;
        $response['message'] = 'Book request submitted successfully. The library will process your request.';
        $response['data'] = [
            'request_id' => $requestId,
            'book_title' => $bookTitle,
            'status' => 'PENDING'
        ];

    } catch (Exception $e) {
        $response['message'] = 'Failed to submit book request: ' . $e->getMessage();
    }
} else {
    $response['message'] = 'Invalid request method. Use POST.';
}

echo json_encode($response);
