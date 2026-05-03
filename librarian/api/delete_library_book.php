<?php
/**
 * POST /librarian/api/delete_library_book.php
 *
 * Delete a library book by ID. Cascading deletes handle tags and departments.
 *
 * Expects JSON body: { "book_id": 123 }
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

    $bookId = intval($body['book_id'] ?? 0);
    if ($bookId <= 0) {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'Valid book_id is required']);
        exit;
    }

    // Check book exists
    $checkStmt = $pdo->prepare("SELECT id, book_title FROM library_books WHERE id = :id");
    $checkStmt->execute([':id' => $bookId]);
    $book = $checkStmt->fetch(PDO::FETCH_ASSOC);

    if (!$book) {
        http_response_code(404);
        echo json_encode(['success' => false, 'message' => 'Book not found']);
        exit;
    }

    // Delete (CASCADE will remove tags and department associations)
    $deleteStmt = $pdo->prepare("DELETE FROM library_books WHERE id = :id");
    $deleteStmt->execute([':id' => $bookId]);

    echo json_encode([
        'success' => true,
        'message' => "Book '{$book['book_title']}' deleted successfully",
    ]);
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Server error: ' . $e->getMessage(),
    ]);
}
