<?php
/**
 * POST /librarian/api/add_category.php
 *
 * Creates a new library category.
 * Body JSON: { "name": "Textbook" }
 */

require_once dirname(__DIR__, 2) . '/bootstrap/database.php';

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Method not allowed']);
    exit;
}

try {
    $pdo  = ascom_get_pdo();
    $body = json_decode(file_get_contents('php://input'), true);

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

    $name = trim($body['name'] ?? '');
    if ($name === '') {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'Category name is required']);
        exit;
    }

    if (mb_strlen($name) > 100) {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'Category name is too long']);
        exit;
    }

    $insert = $pdo->prepare("INSERT INTO library_categories (name, created_at) VALUES (:name, NOW())");
    $insert->execute([':name' => $name]);

    echo json_encode([
        'success' => true,
        'message' => 'Category created',
        'category' => [
            'id' => (int) $pdo->lastInsertId(),
            'name' => $name,
        ],
    ]);
} catch (PDOException $e) {
    // Duplicate name
    if (($e->errorInfo[1] ?? null) === 1062) {
        http_response_code(409);
        echo json_encode(['success' => false, 'message' => 'Category already exists']);
        exit;
    }
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Server error: ' . $e->getMessage()]);
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Server error: ' . $e->getMessage()]);
}
