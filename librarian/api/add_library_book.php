<?php
/**
 * POST /librarian/api/add_library_book.php
 *
 * Add a new book to the library with tags and multi-department assignment.
 *
 * Expects JSON body:
 * {
 *   "book_title": "...",
 *   "author": "...",
 *   "isbn": "...",
 *   "publisher": "...",
 *   "publication_year": "2024",
 *   "edition": "3rd",
 *   "call_number": "CS 001.1",
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

    if (!$body) {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'Invalid JSON body']);
        exit;
    }

    // Validate required fields
    $title = trim($body['book_title'] ?? '');
    if ($title === '') {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'Book title is required']);
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
    $departmentIds    = $body['department_ids'] ?? [];
    $categoryIds      = $body['category_ids'] ?? [];
    $tags             = $body['tags'] ?? [];

    // Primary department = first in the list
    $primaryDeptId = !empty($departmentIds) ? intval($departmentIds[0]) : null;

    // ── Insert book ─────────────────────────────────────────────────────
    $stmt = $pdo->prepare("
        INSERT INTO library_books (
            book_title, author, isbn, publisher, publication_year, edition,
            call_number, classification_id, department_id,
            no_of_copies, available_copies, location, description, status,
            created_at, updated_at
        ) VALUES (
            :title, :author, :isbn, :publisher, :pub_year, :edition,
            :call_number, :class_id, :dept_id,
            :copies, :avail, :location, :description, :status,
            NOW(), NOW()
        )
    ");

    $stmt->execute([
        ':title'       => $title,
        ':author'      => $author,
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

    $bookId = (int) $pdo->lastInsertId();

    // ── Insert department associations ──────────────────────────────────
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

    // ── Insert tags ─────────────────────────────────────────────────────
    if (!empty($tags)) {
        $tagStmt = $pdo->prepare("
            INSERT IGNORE INTO library_book_tags (book_id, tag)
            VALUES (:book_id, :tag)
        ");
        foreach ($tags as $tag) {
            $cleanTag = trim($tag);
            if ($cleanTag !== '') {
                $tagStmt->execute([
                    ':book_id' => $bookId,
                    ':tag'     => strtolower($cleanTag),
                ]);
            }
        }
    }

    // ── Insert category associations ───────────────────────────────────
    if (!empty($categoryIds)) {
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

    echo json_encode([
        'success' => true,
        'message' => 'Book added successfully',
        'book_id' => $bookId,
    ]);
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Server error: ' . $e->getMessage(),
    ]);
}
