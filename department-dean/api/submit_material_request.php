<?php
// submit_material_request.php
// API endpoint for dean to submit material request to librarian

header('Content-Type: application/json');
session_start();
require_once dirname(__FILE__) . '/../includes/db_connection.php';

$response = ['success' => false, 'message' => ''];

// Check if request is POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    $response['message'] = 'Invalid request method';
    echo json_encode($response);
    exit;
}

try {
    $input = json_decode(file_get_contents('php://input'), true);
    
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
    
    // Validate required fields
    $courseId = intval($input['course_id'] ?? 0);
    $bookTitle = trim($input['book_title'] ?? '');
    
    if (empty($courseId)) {
        throw new Exception('Course is required');
    }
    
    if (empty($bookTitle)) {
        throw new Exception('Book title is required');
    }
    
    // Get user ID from session
    $userId = $_SESSION['user_id'] ?? null;
    if (!$userId) {
        throw new Exception('User not authenticated');
    }
    
    // Insert the material request
    $insertQuery = "
        INSERT INTO material_requests (
            course_id,
            requested_by,
            book_title,
            author,
            isbn,
            publisher,
            edition,
            publication_year,
            justification,
            status
        ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, 'pending')
    ";
    
    $stmt = $pdo->prepare($insertQuery);
    $stmt->execute([
        $courseId,
        $userId,
        $bookTitle,
        trim($input['author'] ?? ''),
        trim($input['isbn'] ?? ''),
        trim($input['publisher'] ?? ''),
        trim($input['edition'] ?? ''),
        $input['publication_year'] ?? null,
        trim($input['justification'] ?? '')
    ]);
    
    $response['success'] = true;
    $response['message'] = 'Material request submitted successfully';
    $response['request_id'] = $pdo->lastInsertId();
    
} catch (Exception $e) {
    http_response_code(400);
    $response['message'] = $e->getMessage();
}

echo json_encode($response);
exit;
}

try {
    $pdo = ascom_get_pdo();
    $input = json_decode(file_get_contents('php://input'), true);
    
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
    
    // Validate required fields
    $courseId = intval($input['course_id'] ?? 0);
    $bookTitle = trim($input['book_title'] ?? '');
    
    if (empty($courseId)) {
        throw new Exception('Course is required');
    }
    
    if (empty($bookTitle)) {
        throw new Exception('Book title is required');
    }
    
    // Get user ID from session
    $userId = $_SESSION['user_id'] ?? null;
    if (!$userId) {
        throw new Exception('User not authenticated');
    }
    
    // Insert the material request
    $insertQuery = "
        INSERT INTO material_requests (
            course_id,
            requested_by,
            book_title,
            author,
            isbn,
            publisher,
            edition,
            publication_year,
            justification,
            status
        ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, 'pending')
    ";
    
    $stmt = $pdo->prepare($insertQuery);
    $stmt->execute([
        $courseId,
        $userId,
        $bookTitle,
        trim($input['author'] ?? ''),
        trim($input['isbn'] ?? ''),
        trim($input['publisher'] ?? ''),
        trim($input['edition'] ?? ''),
        $input['publication_year'] ?? null,
        trim($input['justification'] ?? '')
    ]);
    
    $response['success'] = true;
    $response['message'] = 'Material request submitted successfully';
    $response['request_id'] = $pdo->lastInsertId();
    
} catch (Exception $e) {
    http_response_code(400);
    $response['message'] = $e->getMessage();
}

echo json_encode($response);
exit;