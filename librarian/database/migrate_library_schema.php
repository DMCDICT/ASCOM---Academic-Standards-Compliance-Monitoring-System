<?php
/**
 * Migration: Extend library_books schema for the new Library Management system.
 *
 * Creates:
 *   - library_book_tags (book_id, tag)
 *   - library_book_departments (book_id, department_id)
 *   - library_categories (id, name)
 *   - library_book_categories (book_id, category_id)
 *
 * Alters:
 *   - library_books — adds publisher, publication_year, edition, call_number,
 *     location, description, status columns if they don't exist.
 *
 * Safe to run multiple times (idempotent).
 */

require_once dirname(__DIR__, 2) . '/bootstrap/database.php';

$pdo = ascom_get_pdo();
$results = [];

try {
    // Note: DDL statements cause implicit commits in MySQL,
    // so we cannot use transactions here.

    // ── 1. Alter library_books ──────────────────────────────────────────
    $alterColumns = [
        'publisher'        => "VARCHAR(255) DEFAULT NULL AFTER isbn",
        'publication_year' => "VARCHAR(10) DEFAULT NULL AFTER publisher",
        'edition'          => "VARCHAR(50) DEFAULT NULL AFTER publication_year",
        'call_number'      => "VARCHAR(50) DEFAULT NULL AFTER edition",
        'location'         => "VARCHAR(100) DEFAULT NULL AFTER call_number",
        'description'      => "TEXT DEFAULT NULL AFTER location",
        'status'           => "ENUM('available','checked_out','reserved','maintenance','lost') DEFAULT 'available' AFTER description",
    ];

    foreach ($alterColumns as $col => $definition) {
        if (!ascom_table_has_column($pdo, 'library_books', $col)) {
            $pdo->exec("ALTER TABLE library_books ADD COLUMN {$col} {$definition}");
            $results[] = "Added column library_books.{$col}";
        } else {
            $results[] = "Column library_books.{$col} already exists — skipped";
        }
    }

    // ── 2. Create library_book_tags ─────────────────────────────────────
    $pdo->exec("
        CREATE TABLE IF NOT EXISTS library_book_tags (
            id INT AUTO_INCREMENT PRIMARY KEY,
            book_id INT NOT NULL,
            tag VARCHAR(100) NOT NULL,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            INDEX idx_tag (tag),
            INDEX idx_book_id (book_id),
            UNIQUE KEY unique_book_tag (book_id, tag),
            CONSTRAINT fk_lbt_book FOREIGN KEY (book_id)
                REFERENCES library_books(id) ON DELETE CASCADE
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4
    ");
    $results[] = "Table library_book_tags ensured";

    // ── 3. Create library_book_departments ───────────────────────────────
    $pdo->exec("
        CREATE TABLE IF NOT EXISTS library_book_departments (
            id INT AUTO_INCREMENT PRIMARY KEY,
            book_id INT NOT NULL,
            department_id INT NOT NULL,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            UNIQUE KEY unique_book_dept (book_id, department_id),
            CONSTRAINT fk_lbd_book FOREIGN KEY (book_id)
                REFERENCES library_books(id) ON DELETE CASCADE,
            CONSTRAINT fk_lbd_dept FOREIGN KEY (department_id)
                REFERENCES departments(id) ON DELETE CASCADE
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4
    ");
    $results[] = "Table library_book_departments ensured";

    // ── 4. Create library_categories ───────────────────────────────────
    $pdo->exec("
        CREATE TABLE IF NOT EXISTS library_categories (
            id INT AUTO_INCREMENT PRIMARY KEY,
            name VARCHAR(100) NOT NULL,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            UNIQUE KEY unique_library_category_name (name),
            INDEX idx_name (name)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4
    ");
    $results[] = "Table library_categories ensured";

    // ── 5. Create library_book_categories ───────────────────────────────
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
    $results[] = "Table library_book_categories ensured";

    $results[] = "Migration completed successfully";
} catch (Exception $e) {
    $results[] = "ERROR: " . $e->getMessage();
}

// Output results
header('Content-Type: application/json');
echo json_encode(['results' => $results], JSON_PRETTY_PRINT);
