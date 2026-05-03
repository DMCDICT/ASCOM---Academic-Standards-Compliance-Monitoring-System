<?php
/**
 * GET /librarian/api/get_categories.php
 *
 * Returns library categories.
 * Response: { success: true, data: [{ id, name }] }
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

    $stmt = $pdo->query("
        SELECT id, name
        FROM library_categories
        ORDER BY name ASC
    ");

    echo json_encode([
        'success' => true,
        'data' => $stmt->fetchAll(PDO::FETCH_ASSOC),
    ]);
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Server error: ' . $e->getMessage()]);
}
