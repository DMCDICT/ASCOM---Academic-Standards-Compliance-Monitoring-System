<?php
// get_library_books.php
// API endpoint for fetching library books catalog with search and pagination

header('Content-Type: application/json');
ini_set('display_errors', 0);
require_once dirname(__FILE__) . '/../includes/db_connection.php';

$response = ['success' => false, 'message' => '', 'books' => [], 'total' => 0, 'page' => 1, 'limit' => 20];

try {
    // Ensure classifications table exists
    $pdo->exec("
        CREATE TABLE IF NOT EXISTS `classifications` (
          `id` INT(11) NOT NULL AUTO_INCREMENT,
          `name` VARCHAR(255) NOT NULL,
          `type` VARCHAR(50) NOT NULL DEFAULT 'DDC',
          `call_number_range` VARCHAR(20) NOT NULL,
          `description` TEXT NULL,
          `status` ENUM('active', 'inactive') NOT NULL DEFAULT 'active',
          `created_by` INT(11) NULL,
          `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
          `updated_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
          PRIMARY KEY (`id`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4
    ");
    
    // Ensure library_books table exists
    $pdo->exec("
        CREATE TABLE IF NOT EXISTS `library_books` (
          `id` INT(11) NOT NULL AUTO_INCREMENT,
          `book_title` VARCHAR(255) NOT NULL,
          `author` VARCHAR(255) DEFAULT NULL,
          `isbn` VARCHAR(50) DEFAULT NULL,
          `publisher` VARCHAR(255) DEFAULT NULL,
          `publication_year` VARCHAR(10) DEFAULT NULL,
          `edition` VARCHAR(50) DEFAULT NULL,
          `call_number` VARCHAR(100) DEFAULT NULL,
          `location` VARCHAR(255) DEFAULT NULL,
          `classification_id` INT(11) DEFAULT NULL,
          `status` VARCHAR(50) DEFAULT 'available',
          `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
          PRIMARY KEY (`id`),
          INDEX (`classification_id`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4
    ");
    
    $search = trim($_GET['search'] ?? '');
    $page = max(1, intval($_GET['page'] ?? 1));
    $limit = min(intval($_GET['limit'] ?? 20), 50);
    $offset = ($page - 1) * $limit;
    
    // Build WHERE clause
    $conditions = [];
    $params = [];
    
    if (!empty($search)) {
        $conditions[] = "(lb.book_title LIKE ? OR lb.author LIKE ? OR lb.isbn LIKE ? OR lb.call_number LIKE ?)";
        $likeSearch = '%' . $search . '%';
        $params[] = $likeSearch;
        $params[] = $likeSearch;
        $params[] = $likeSearch;
        $params[] = $likeSearch;
    }
    
    $whereClause = !empty($conditions) ? 'WHERE ' . implode(' AND ', $conditions) : '';
    
    // Get total count
    $countSQL = "SELECT COUNT(*) as total FROM library_books lb {$whereClause}";
    $countStmt = $pdo->prepare($countSQL);
    $countStmt->execute($params);
    $total = $countStmt->fetch(PDO::FETCH_ASSOC)['total'] ?? 0;
    
    // Get books with pagination
    $querySQL = "
        SELECT 
            lb.id,
            lb.book_title,
            lb.author,
            lb.isbn,
            lb.publisher,
            lb.publication_year,
            lb.edition,
            lb.call_number,
            lb.location,
            lb.status,
            COALESCE(c.name, 'Uncategorized') AS classification_name,
            COALESCE(c.call_number_range, '') AS classification_range
        FROM library_books lb
        LEFT JOIN classifications c ON lb.classification_id = c.id
        {$whereClause}
        ORDER BY lb.book_title ASC
        LIMIT {$limit} OFFSET {$offset}
    ";
    
    $stmt = $pdo->prepare($querySQL);
    $stmt->execute($params);
    $books = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Format response
    foreach ($books as &$book) {
        $book['title'] = htmlspecialchars($book['book_title'] ?? '');
        $book['authors'] = htmlspecialchars($book['author'] ?? '');
        $book['copyright_year'] = $book['publication_year'] ? intval($book['publication_year']) : null;
        $book['display_title'] = htmlspecialchars($book['book_title'] ?? '');
        $book['display_authors'] = htmlspecialchars($book['author'] ?? '');
        $book['display_year'] = $book['publication_year'] ? htmlspecialchars($book['publication_year']) : 'N/A';
        $book['display_info'] = htmlspecialchars(($book['publisher'] ?? 'Unknown Publisher') . ' (' . ($book['publication_year'] ?? 'N/A') . ')');
        $book['display_location'] = htmlspecialchars(($book['call_number'] ?? '') . ' - ' . ($book['location'] ?? ''));
        $book['display_classification'] = $book['classification_name'] ? htmlspecialchars($book['classification_name']) : 'N/A';
    }
    
    $response['success'] = true;
    $response['books'] = $books;
    $response['total'] = intval($total);
    $response['page'] = $page;
    $response['limit'] = $limit;
    $response['totalPages'] = ceil($total / $limit);
    
} catch (Exception $e) {
    $response['message'] = 'Error fetching books: ' . $e->getMessage();
    error_log('get_library_books.php error: ' . $e->getMessage());
}

echo json_encode($response);