<?php
// update_material_request.php
// API endpoint for librarian to update material request status

header('Content-Type: application/json');
require_once dirname(__FILE__) . '/../includes/db_connection.php';
require_once dirname(dirname(__FILE__)) . '/../session_config.php';

$response = ['success' => false, 'message' => ''];

// Check if request is POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    $response['message'] = 'Invalid request method';
    echo json_encode($response);
    exit;
}

try {
    // Auto-create table if it doesn't exist
    $tableCheck = $pdo->query("SHOW TABLES LIKE 'material_requests'");
    if ($tableCheck->rowCount() == 0) {
        $createTableSQL = "CREATE TABLE IF NOT EXISTS `material_requests` (
            `id` INT(11) NOT NULL AUTO_INCREMENT,
            `course_id` INT(11) NOT NULL,
            `requested_by` INT(11) NULL,
            `book_title` VARCHAR(500) NOT NULL,
            `author` VARCHAR(255) NULL,
            `isbn` VARCHAR(50) NULL,
            `publisher` VARCHAR(255) NULL,
            `edition` VARCHAR(100) NULL,
            `publication_year` YEAR NULL,
            `justification` TEXT NULL,
            `status` ENUM('pending', 'processing', 'completed', 'rejected') NOT NULL DEFAULT 'pending',
            `librarian_notes` TEXT NULL,
            `processed_by` INT(11) NULL,
            `processed_at` DATETIME NULL,
            `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
            `updated_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            PRIMARY KEY (`id`),
            KEY `idx_course_id` (`course_id`),
            KEY `idx_requested_by` (`requested_by`),
            KEY `idx_status` (`status`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci";
        $pdo->exec($createTableSQL);
    }
    
    $input = json_decode(file_get_contents('php://input'), true);
    
    $requestId = intval($input['id'] ?? 0);
    $status = trim($input['status'] ?? '');
    $librarianNotes = trim($input['librarian_notes'] ?? '');
    
    if (empty($requestId)) {
        throw new Exception('Request ID is required');
    }
    
    $validStatuses = ['pending', 'processing', 'completed', 'rejected'];
    if (!in_array($status, $validStatuses)) {
        throw new Exception('Invalid status');
    }
    
    // Get librarian user ID
    $librarianId = $_SESSION['user_id'] ?? null;
    
    // Update the material request
    $updateQuery = "
        UPDATE material_requests SET
            status = ?,
            librarian_notes = ?,
            processed_by = ?,
            processed_at = NOW()
        WHERE id = ?
    ";
    
    $stmt = $pdo->prepare($updateQuery);
    $stmt->execute([$status, $librarianNotes, $librarianId, $requestId]);
    
    $response['success'] = true;
    $response['message'] = 'Material request updated successfully';
    
} catch (Exception $e) {
    http_response_code(400);
    $response['message'] = $e->getMessage();
}

echo json_encode($response);
exit;