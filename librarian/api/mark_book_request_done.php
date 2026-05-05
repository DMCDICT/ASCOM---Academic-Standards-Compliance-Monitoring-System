<?php
/**
 * mark_book_request_done.php
 * API for librarian to mark a book request as done
 */

// Disable error display, return JSON instead
error_reporting(0);
ini_set('display_errors', 0);

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST');
header('Access-Control-Allow-Headers: Content-Type');

try {
    // Ensure we use the same session cookie/name as the rest of the app (ASCOM_SESSION).
    require_once dirname(__DIR__, 2) . '/session_config.php';
    require_once dirname(__DIR__, 2) . '/bootstrap/database.php';
    $pdo = ascom_get_pdo();
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => 'Database connection failed: ' . $e->getMessage()]);
    exit;
}

// Start session
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
    $response['message'] = 'Access denied. Only librarians can process book requests.';
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
        
        // Handle bulk requests
        $requestIds = $input['request_ids'] ?? null;
        $singleRequestId = $input['request_id'] ?? null;
        
        if ($requestIds && is_array($requestIds)) {
            // Bulk processing
            if (empty($requestIds)) {
                $response['message'] = 'No request IDs provided.';
                echo json_encode($response);
                exit;
            }
            
            // Update all requests
            $placeholders = implode(',', array_fill(0, count($requestIds), '?'));
            $updateQuery = "UPDATE book_requests 
                SET status = 'DONE', processed_by = ?, processed_at = NOW() 
                WHERE id IN ($placeholders) AND status = 'PENDING'";
            
            $updateStmt = $pdo->prepare($updateQuery);
            $updateStmt->execute(array_merge([$_SESSION['user_id']], $requestIds));
            
            $affectedRows = $updateStmt->rowCount();
            
            $response['success'] = true;
            $response['message'] = "$affectedRows request(s) marked as done.";
            $response['data'] = [
                'processed_count' => $affectedRows,
                'requested_count' => count($requestIds)
            ];
            
        } elseif ($singleRequestId) {
            // Single request processing
            // Get the book request
            $requestQuery = "SELECT * FROM book_requests WHERE id = ? AND status = 'PENDING'";
            $requestStmt = $pdo->prepare($requestQuery);
            $requestStmt->execute([$singleRequestId]);
            $bookRequest = $requestStmt->fetch(PDO::FETCH_ASSOC);
            
            if (!$bookRequest) {
                $response['message'] = 'Book request not found or already processed.';
                echo json_encode($response);
                exit;
            }
            
            // Update book_requests status to DONE
            $updateRequest = "UPDATE book_requests 
                SET status = 'DONE', processed_by = ?, processed_at = NOW() 
                WHERE id = ?";
            
            $updateStmt = $pdo->prepare($updateRequest);
            $updateStmt->execute([$_SESSION['user_id'], $singleRequestId]);
            
            $response['success'] = true;
            $response['message'] = 'Book request marked as done.';
            $response['data'] = [
                'request_id' => $singleRequestId,
                'book_title' => $bookRequest['book_title'],
                'status' => 'DONE'
            ];
        } else {
            $response['message'] = 'Request ID or Request IDs are required.';
            echo json_encode($response);
            exit;
        }
        
    } catch (Exception $e) {
        $response['message'] = 'Failed to process book request: ' . $e->getMessage();
    }
} else {
    $response['message'] = 'Invalid request method. Use POST.';
}

echo json_encode($response);
