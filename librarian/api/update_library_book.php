<?php
/**
 * POST /librarian/api/update_library_book.php
 *
 * Update an existing library book and sync tags/departments/categories.
 *
 * Expects JSON body:
 * {
 *   "book_id": 123,
 *   "book_title": "...",
 *   "author": "...",
 *   "isbn": "...",
 *   "publisher": "...",
 *   "publication_year": "2024",
 *   "edition": "3rd",
 *   "call_number": "004.6 D569",
 *   "classification_id": 11,
 *   "no_of_copies": 5,
 *   "available_copies": 5,
 *   "location": "Main Library",
 *   "description": "...",
 *   "status": "available",
 *   "department_ids": [1, 2],
 *   "category_ids": [3, 4],
 *   "tags": ["programming", "algorithms"]
 * }
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

    $title = trim($body['book_title'] ?? '');
    if ($title === '') {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'Book title is required']);
        exit;
    }

    // Verify book exists
    $existsStmt = $pdo->prepare("SELECT id FROM library_books WHERE id = :id");
    $existsStmt->execute([':id' => $bookId]);
    if (!$existsStmt->fetchColumn()) {
        http_response_code(404);
        echo json_encode(['success' => false, 'message' => 'Book not found']);
        exit;
    }

    $author           = trim($body['author'] ?? '');
    $isbn             = trim($body['isbn'] ?? '');
    $publisher        = trim($body['publisher'] ?? '');
    $publicationYear  = trim($body['publication_year'] ?? '');
    $edition          = trim($body['edition'] ?? '');
    $callNumber       = trim($body['call_number'] ?? '');
    $classificationId = !empty($body['classification_id']) ? intval($body['classification_id']) : null;
    $noOfCopies       = max(0, intval($body['no_of_copies'] ?? 1));
    $availableCopies  = max(0, intval($body['available_copies'] ?? $noOfCopies));
    $location         = trim($body['location'] ?? '');
    $description      = trim($body['description'] ?? '');
    $status           = trim($body['status'] ?? 'available');
    $departmentIds    = is_array($body['department_ids'] ?? null) ? $body['department_ids'] : [];
    $categoryIds      = is_array($body['category_ids'] ?? null) ? $body['category_ids'] : [];
    $tags             = is_array($body['tags'] ?? null) ? $body['tags'] : [];

    $primaryDeptId = !empty($departmentIds) ? intval($departmentIds[0]) : null;

    // Update main book row
    $update = $pdo->prepare("
        UPDATE library_books SET
            book_title = :title,
            author = :author,
            isbn = :isbn,
            publisher = :publisher,
            publication_year = :pub_year,
            edition = :edition,
            call_number = :call_number,
            classification_id = :class_id,
            department_id = :dept_id,
            no_of_copies = :copies,
            available_copies = :avail,
            location = :location,
            description = :description,
            status = :status,
            updated_at = NOW()
        WHERE id = :id
    ");

    $update->execute([
        ':id'          => $bookId,
        ':title'       => $title,
        ':author'      => $author ?: null,
        ':isbn'        => $isbn ?: null,
        ':publisher'   => $publisher ?: null,
        ':pub_year'    => $publicationYear ?: null,
        ':edition'     => $edition ?: null,
        ':call_number' => $callNumber ?: null,
        ':class_id'    => $classificationId,
        ':dept_id'     => $primaryDeptId,
        ':copies'      => $noOfCopies,
        ':avail'       => $availableCopies,
        ':location'    => $location ?: null,
        ':description' => $description ?: null,
        ':status'      => $status,
    ]);

    // Sync departments
    $pdo->prepare("DELETE FROM library_book_departments WHERE book_id = :id")
        ->execute([':id' => $bookId]);
    if (!empty($departmentIds)) {
        $deptStmt = $pdo->prepare("
            INSERT IGNORE INTO library_book_departments (book_id, department_id)
            VALUES (:book_id, :dept_id)
        ");
        foreach ($departmentIds as $deptId) {
            $deptStmt->execute([
                ':book_id' => $bookId,
                ':dept_id' => intval($deptId),
            ]);
        }
    }

    // Sync tags
    $pdo->prepare("DELETE FROM library_book_tags WHERE book_id = :id")
        ->execute([':id' => $bookId]);
    if (!empty($tags)) {
        $tagStmt = $pdo->prepare("
            INSERT IGNORE INTO library_book_tags (book_id, tag)
            VALUES (:book_id, :tag)
        ");
        foreach ($tags as $tag) {
            $cleanTag = strtolower(trim($tag));
            if ($cleanTag !== '') {
                $tagStmt->execute([
                    ':book_id' => $bookId,
                    ':tag' => $cleanTag,
                ]);
            }
        }
    }

    // Sync categories
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

    $pdo->prepare("DELETE FROM library_book_categories WHERE book_id = :id")
        ->execute([':id' => $bookId]);
    if (!empty($categoryIds)) {
        $catStmt = $pdo->prepare("
            INSERT IGNORE INTO library_book_categories (book_id, category_id)
            VALUES (:book_id, :category_id)
        ");
        foreach ($categoryIds as $categoryId) {
            $catId = intval($categoryId);
            if ($catId > 0) {
                $catStmt->execute([
                    ':book_id' => $bookId,
                    ':category_id' => $catId,
                ]);
            }
        }
    }

    echo json_encode(['success' => true, 'message' => 'Book updated successfully']);
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Server error: ' . $e->getMessage()]);
}
