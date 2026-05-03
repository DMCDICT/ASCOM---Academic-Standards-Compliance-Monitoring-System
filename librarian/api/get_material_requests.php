<?php
// get_material_requests.php
// API endpoint to fetch material requests for librarian

header('Content-Type: application/json');
require_once dirname(__FILE__) . '/../includes/db_connection.php';

$response = ['success' => false, 'data' => [], 'message' => ''];

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
    
    // Get status filter from query parameter (default: pending)
    $statusFilter = $_GET['status'] ?? 'pending';
    
    // Validate status filter
    $validStatuses = ['pending', 'processing', 'completed', 'rejected'];
    if (!in_array($statusFilter, $validStatuses)) {
        $statusFilter = 'pending';
    }
    
    // Fetch material requests
    $query = "
        SELECT 
            mr.id,
            mr.book_title,
            mr.author,
            mr.isbn,
            mr.publisher,
            mr.edition,
            mr.publication_year,
            mr.justification,
            mr.status,
            mr.librarian_notes,
            mr.processed_at,
            mr.created_at,
            mr.course_id,
            c.course_code,
            c.course_title,
            CONCAT(COALESCE(u.first_name, ''), ' ', COALESCE(u.last_name, '')) AS requester_name,
            d.department_code,
            d.color_code AS department_color
        FROM material_requests mr
        LEFT JOIN courses c ON mr.course_id = c.id
        LEFT JOIN users u ON mr.requested_by = u.id
        LEFT JOIN departments d ON u.department_id = d.id
        WHERE mr.status = ?
        ORDER BY mr.created_at DESC
    ";
    
    $stmt = $pdo->prepare($query);
    $stmt->execute([$statusFilter]);
    $requests = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    $response['success'] = true;
    $response['data'] = $requests;
    $response['message'] = 'Material requests fetched successfully';
    
} catch (Exception $e) {
    $response['message'] = 'Failed to fetch material requests: ' . $e->getMessage();
}

echo json_encode($response);
exit;