-- Adds category support for Librarian Library Management (idempotent).
-- Safe to run multiple times.

CREATE TABLE IF NOT EXISTS library_categories (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    UNIQUE KEY unique_library_category_name (name),
    INDEX idx_name (name)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

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
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

