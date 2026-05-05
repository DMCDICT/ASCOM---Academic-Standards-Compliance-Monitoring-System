<?php
/**
 * get_dean_book_requests.php
 * API for dean to get all their book requests with status filtering
 */

header('Content-Type: application/json');
require_once dirname(__FILE__) . '/../../session_config.php';
require_once dirname(__FILE__) . '/../includes/db_connection.php';

// session_config.php configures cookies but doesn't always start the session.
// API endpoints must start it to access $_SESSION.
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$response = ['success' => false, 'message' => '', 'data' => [], 'counts' => []];

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
    $response['message'] = 'Access denied. Only deans can view their book requests.';
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

if ($_SERVER['REQUEST_METHOD'] === 'GET' || $_SERVER['REQUEST_METHOD'] === 'POST') {
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
        
        // Get status filter from query params
        $statusFilter = $_GET['status'] ?? $_POST['status'] ?? 'PENDING';
        
        // Build query based on filter
        $query = "
            SELECT 
                id,
                book_title,
                author,
                isbn,
                publisher,
                publication_year,
                edition,
                reason,
                status,
                requested_at,
                processed_at
            FROM book_requests 
            WHERE department_code = ?
        ";
        
        $params = [$departmentCode];
        
        // Add status filter if provided and valid
        $validStatuses = ['PENDING', 'DONE', 'ALL'];
        if ($statusFilter && in_array($statusFilter, $validStatuses)) {
            if ($statusFilter !== 'ALL') {
                $query .= " AND status = ?";
                $params[] = $statusFilter;
            }
        } else {
            // Default to PENDING
            $query .= " AND status = 'PENDING'";
        }
        
        $query .= " ORDER BY requested_at DESC";
        
        $stmt = $pdo->prepare($query);
        $stmt->execute($params);
        $requests = $stmt->fetchAll(PDO::FETCH_ASSOC);

        // Get counts for all statuses
        $countQuery = "
            SELECT 
                status,
                COUNT(*) as count
            FROM book_requests 
            WHERE department_code = ?
            GROUP BY status
        ";
        $countStmt = $pdo->prepare($countQuery);
        $countStmt->execute([$departmentCode]);
        $counts = $countStmt->fetchAll(PDO::FETCH_ASSOC);
        
        $countsArray = [
            'total' => 0,
            'pending' => 0,
            'done' => 0
        ];
        
        foreach ($counts as $row) {
            $countsArray['total'] += (int)$row['count'];
            $statusKey = strtolower($row['status']);
            $countsArray[$statusKey] = (int)$row['count'];
        }

        $response['success'] = true;
        $response['message'] = 'Book requests fetched successfully';
        $response['data'] = $requests;
        $response['counts'] = $countsArray;

    } catch (Exception $e) {
        $response['message'] = 'Failed to fetch book requests: ' . $e->getMessage();
    }
} else {
    $response['message'] = 'Invalid request method.';
}

echo json_encode($response);
