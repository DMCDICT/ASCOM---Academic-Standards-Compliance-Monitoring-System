<?php
/**
 * get_pending_book_requests.php
 * API for librarian to get all book requests from deans
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

// Check if user is logged in and is a librarian
if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'Authentication required']);
    exit;
}

$userRoleType = strtolower((string) ($_SESSION['user_role'] ?? ''));
$selectedRole = is_array($_SESSION['selected_role'] ?? null) ? $_SESSION['selected_role'] : [];
$selectedRoleType = strtolower((string) ($selectedRole['type'] ?? ($selectedRole['role_name'] ?? '')));

if (empty($_SESSION['librarian_logged_in']) && $userRoleType !== 'librarian' && $selectedRoleType !== 'librarian') {
    echo json_encode(['success' => false, 'message' => 'Access denied. Librarians only.']);
    exit;
}

$response = ['success' => false, 'message' => '', 'data' => [], 'counts' => []];

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
    
    // Get status filter
    $statusFilter = $_GET['status'] ?? 'PENDING';
    
    // Build query
    $query = "
        SELECT 
            br.id,
            br.requesting_dean_id,
            br.department_code,
            br.book_title,
            br.author,
            br.isbn,
            br.publisher,
            br.publication_year,
            br.edition,
            br.reason,
            br.status,
            br.requested_at,
            br.processed_at,
            CONCAT(u.first_name, ' ', u.last_name) as dean_name
        FROM book_requests br
        LEFT JOIN users u ON br.requesting_dean_id = u.id
        WHERE 1=1
    ";
    
    $params = [];
    
    // Add status filter
    $validStatuses = ['PENDING', 'DONE', 'ALL'];
    if ($statusFilter && in_array($statusFilter, $validStatuses)) {
        if ($statusFilter !== 'ALL') {
            $query .= " AND br.status = ?";
            $params[] = $statusFilter;
        }
    } else {
        $query .= " AND br.status = 'PENDING'";
    }
    
    $query .= " ORDER BY br.requested_at DESC";
    
    $stmt = $pdo->prepare($query);
    $stmt->execute($params);
    $requests = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Get counts
    $countQuery = "
        SELECT 
            status,
            COUNT(*) as count
        FROM book_requests 
        GROUP BY status
    ";
    $countStmt = $pdo->query($countQuery);
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

echo json_encode($response);
