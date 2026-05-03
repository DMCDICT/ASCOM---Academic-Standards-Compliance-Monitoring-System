<?php
/**
 * GET /librarian/api/get_library_book.php?id=123
 *
 * Fetch a single library book with tags, departments, and categories.
 */

require_once dirname(__DIR__, 2) . '/bootstrap/database.php';

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Method not allowed']);
    exit;
}

try {
    $pdo = ascom_get_pdo();
    $id = intval($_GET['id'] ?? 0);
    if ($id <= 0) {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'Valid id is required']);
        exit;
    }

    $stmt = $pdo->prepare("
        SELECT
            lb.*,
            COALESCE(c.name, 'Uncategorized') AS classification_name,
            COALESCE(c.call_number_range, '') AS classification_range,
            COALESCE(d.department_name, 'N/A') AS primary_department_name,
            COALESCE(d.department_code, '') AS primary_department_code,
            COALESCE(d.color_code, '#666') AS primary_department_color
        FROM library_books lb
        LEFT JOIN classifications c ON lb.classification_id = c.id
        LEFT JOIN departments d ON lb.department_id = d.id
        WHERE lb.id = :id
        LIMIT 1
    ");
    $stmt->execute([':id' => $id]);
    $book = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$book) {
        http_response_code(404);
        echo json_encode(['success' => false, 'message' => 'Book not found']);
        exit;
    }

    // Tags
    $tagsStmt = $pdo->prepare("SELECT tag FROM library_book_tags WHERE book_id = :id ORDER BY tag ASC");
    $tagsStmt->execute([':id' => $id]);
    $tags = array_map(fn ($r) => $r['tag'], $tagsStmt->fetchAll(PDO::FETCH_ASSOC));

    // Departments
    $deptStmt = $pdo->prepare("
        SELECT d.id, d.department_name AS name, d.department_code AS code, d.color_code AS color
        FROM library_book_departments lbd
        JOIN departments d ON d.id = lbd.department_id
        WHERE lbd.book_id = :id
        ORDER BY d.department_name ASC
    ");
    $deptStmt->execute([':id' => $id]);
    $departments = $deptStmt->fetchAll(PDO::FETCH_ASSOC);

    // Categories
    // Ensure tables exist (safe, idempotent)
    $pdo->exec("
        CREATE TABLE IF NOT EXISTS library_categories (
            id INT AUTO_INCREMENT PRIMARY KEY,
            name VARCHAR(100) NOT NULL,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            UNIQUE KEY unique_library_category_name (name),
            INDEX idx_name (name)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4
    ");
    $pdo->exec("
        CREATE TABLE IF NOT EXISTS library_book_categories (
            id INT AUTO_INCREMENT PRIMARY KEY,
            book_id INT NOT NULL,
            category_id INT NOT NULL,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            UNIQUE KEY unique_book_category (book_id, category_id),
            INDEX idx_book_id (book_id),
            INDEX idx_category_id (category_id),
            CONSTRAINT fk_lbc_book FOREIGN KEY (book_id)
                REFERENCES library_books(id) ON DELETE CASCADE,
            CONSTRAINT fk_lbc_category FOREIGN KEY (category_id)
                REFERENCES library_categories(id) ON DELETE CASCADE
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4
    ");

    $catStmt = $pdo->prepare("
        SELECT lc.id, lc.name
        FROM library_book_categories lbc
        JOIN library_categories lc ON lc.id = lbc.category_id
        WHERE lbc.book_id = :id
        ORDER BY lc.name ASC
    ");
    $catStmt->execute([':id' => $id]);
    $categories = $catStmt->fetchAll(PDO::FETCH_ASSOC);

    // Ensure primary department appears in departments list first
    if (!empty($book['department_id']) && !empty($book['primary_department_code'])) {
        $primaryId = (int) $book['department_id'];
        $exists = false;
        foreach ($departments as $d) {
            if ((int) $d['id'] === $primaryId) {
                $exists = true;
                break;
            }
        }
        if (!$exists) {
            array_unshift($departments, [
                'id' => $primaryId,
                'name' => $book['primary_department_name'],
                'code' => $book['primary_department_code'],
                'color' => $book['primary_department_color'],
            ]);
        }
    }

    $book['tags'] = $tags;
    $book['departments'] = $departments;
    $book['categories'] = $categories;

    echo json_encode(['success' => true, 'data' => $book]);
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Server error: ' . $e->getMessage()]);
}
