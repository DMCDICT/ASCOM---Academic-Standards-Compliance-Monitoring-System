<?php
/**
 * GET /librarian/api/get_books.php
 *
 * Fetch paginated library books with optional filters.
 *
 * Query params:
 *   search    – search term (matches title, author, isbn, tags)
 *   department – department ID or 'all'
 *   category  – category ID or 'all'
 *   classification – classification ID or 'all'
 *   status    – book status or 'all'
 *   page      – page number (1-based, default 1)
 *   limit     – items per page (default 10)
 *
 * Returns JSON: { success, data: [...], total, page, limit, totalPages, stats }
 */

require_once dirname(__DIR__, 2) . '/bootstrap/database.php';

header('Content-Type: application/json');

try {
    $pdo = ascom_get_pdo();

    // Ensure category tables exist (safe, idempotent)
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

    // Parse parameters
    $search         = trim($_GET['search'] ?? '');
    $department      = $_GET['department'] ?? 'all';
    $category        = $_GET['category'] ?? 'all';
    $classification  = $_GET['classification'] ?? 'all';
    $status          = $_GET['status'] ?? 'all';
    $page            = max(1, intval($_GET['page'] ?? 1));
    $limit           = max(1, min(100, intval($_GET['limit'] ?? 10)));
    $offset          = ($page - 1) * $limit;

    // ── Build WHERE clauses ─────────────────────────────────────────────
    $conditions = [];
    $params     = [];

    if ($search !== '') {
        $conditions[] = "(
            lb.book_title LIKE :search
            OR lb.author LIKE :search2
            OR lb.isbn LIKE :search3
            OR lb.publisher LIKE :search4
            OR EXISTS (
                SELECT 1 FROM library_book_tags lbt
                WHERE lbt.book_id = lb.id AND lbt.tag LIKE :search5
            )
            OR EXISTS (
                SELECT 1
                FROM library_book_categories lbc
                JOIN library_categories lc ON lc.id = lbc.category_id
                WHERE lbc.book_id = lb.id AND lc.name LIKE :search6
            )
        )";
        $searchTerm = "%{$search}%";
        $params[':search']  = $searchTerm;
        $params[':search2'] = $searchTerm;
        $params[':search3'] = $searchTerm;
        $params[':search4'] = $searchTerm;
        $params[':search5'] = $searchTerm;
        $params[':search6'] = $searchTerm;
    }

    if ($department !== 'all' && $department !== '') {
        $conditions[] = "(
            lb.department_id = :dept_id
            OR EXISTS (
                SELECT 1 FROM library_book_departments lbd
                WHERE lbd.book_id = lb.id AND lbd.department_id = :dept_id2
            )
        )";
        $params[':dept_id']  = intval($department);
        $params[':dept_id2'] = intval($department);
    }

    if ($category !== 'all' && $category !== '') {
        $conditions[] = "EXISTS (
            SELECT 1 FROM library_book_categories lbc
            WHERE lbc.book_id = lb.id AND lbc.category_id = :cat_id
        )";
        $params[':cat_id'] = intval($category);
    }

    if ($classification !== 'all' && $classification !== '') {
        $conditions[] = "lb.classification_id = :class_id";
        $params[':class_id'] = intval($classification);
    }

    if ($status !== 'all' && $status !== '') {
        $conditions[] = "lb.status = :status";
        $params[':status'] = $status;
    }

    $whereSQL = count($conditions) > 0
        ? 'WHERE ' . implode(' AND ', $conditions)
        : '';

    // ── Count total ─────────────────────────────────────────────────────
    $countSQL = "SELECT COUNT(DISTINCT lb.id) FROM library_books lb {$whereSQL}";
    $countStmt = $pdo->prepare($countSQL);
    $countStmt->execute($params);
    $total = (int) $countStmt->fetchColumn();

    // ── Fetch books ─────────────────────────────────────────────────────
    $dataSQL = "
        SELECT
            lb.id,
            lb.book_title,
            lb.author,
            lb.isbn,
            lb.publisher,
            lb.publication_year,
            lb.edition,
            lb.call_number,
            lb.no_of_copies,
            lb.available_copies,
            lb.location,
            lb.description,
            lb.status,
            lb.classification_id,
            lb.department_id,
            lb.created_at,
            lb.updated_at,
            COALESCE(c.name, 'Uncategorized') AS classification_name,
            COALESCE(c.call_number_range, '') AS classification_range,
            COALESCE(d.department_name, 'N/A') AS primary_department_name,
            COALESCE(d.department_code, '') AS primary_department_code,
            COALESCE(d.color_code, '#666') AS primary_department_color
        FROM library_books lb
        LEFT JOIN classifications c ON lb.classification_id = c.id
        LEFT JOIN departments d ON lb.department_id = d.id
        {$whereSQL}
        ORDER BY lb.created_at DESC
        LIMIT :limit OFFSET :offset
    ";

    $dataStmt = $pdo->prepare($dataSQL);
    foreach ($params as $key => $val) {
        $dataStmt->bindValue($key, $val);
    }
    $dataStmt->bindValue(':limit', $limit, PDO::PARAM_INT);
    $dataStmt->bindValue(':offset', $offset, PDO::PARAM_INT);
    $dataStmt->execute();
    $books = $dataStmt->fetchAll(PDO::FETCH_ASSOC);

    // ── Attach tags and departments to each book ────────────────────────
    if (!empty($books)) {
        $bookIds = array_column($books, 'id');
        $placeholders = implode(',', array_fill(0, count($bookIds), '?'));

        // Tags
        $tagStmt = $pdo->prepare("
            SELECT book_id, tag FROM library_book_tags
            WHERE book_id IN ({$placeholders})
            ORDER BY tag ASC
        ");
        $tagStmt->execute($bookIds);
        $tagRows = $tagStmt->fetchAll(PDO::FETCH_ASSOC);
        $tagMap = [];
        foreach ($tagRows as $row) {
            $tagMap[$row['book_id']][] = $row['tag'];
        }

        // Departments (additional)
        $deptStmt = $pdo->prepare("
            SELECT lbd.book_id, d.id AS dept_id, d.department_name, d.department_code, d.color_code
            FROM library_book_departments lbd
            JOIN departments d ON lbd.department_id = d.id
            WHERE lbd.book_id IN ({$placeholders})
            ORDER BY d.department_name ASC
        ");
        $deptStmt->execute($bookIds);
        $deptRows = $deptStmt->fetchAll(PDO::FETCH_ASSOC);
        $deptMap = [];
        foreach ($deptRows as $row) {
            $deptMap[$row['book_id']][] = [
                'id'   => $row['dept_id'],
                'name' => $row['department_name'],
                'code' => $row['department_code'],
                'color' => $row['color_code'],
            ];
        }

        // Categories
        $catStmt = $pdo->prepare("
            SELECT lbc.book_id, lc.id AS category_id, lc.name
            FROM library_book_categories lbc
            JOIN library_categories lc ON lbc.category_id = lc.id
            WHERE lbc.book_id IN ({$placeholders})
            ORDER BY lc.name ASC
        ");
        $catStmt->execute($bookIds);
        $catRows = $catStmt->fetchAll(PDO::FETCH_ASSOC);
        $catMap = [];
        foreach ($catRows as $row) {
            $catMap[$row['book_id']][] = [
                'id' => (int) $row['category_id'],
                'name' => $row['name'],
            ];
        }

        // Merge into books
        foreach ($books as &$book) {
            $book['tags'] = $tagMap[$book['id']] ?? [];
            $book['departments'] = $deptMap[$book['id']] ?? [];
            $book['categories'] = $catMap[$book['id']] ?? [];
            // Include primary department in the departments list if not already
            if ($book['department_id'] && $book['primary_department_code']) {
                $primaryAlreadyIncluded = false;
                foreach ($book['departments'] as $d) {
                    if ($d['id'] == $book['department_id']) {
                        $primaryAlreadyIncluded = true;
                        break;
                    }
                }
                if (!$primaryAlreadyIncluded) {
                    array_unshift($book['departments'], [
                        'id'    => $book['department_id'],
                        'name'  => $book['primary_department_name'],
                        'code'  => $book['primary_department_code'],
                        'color' => $book['primary_department_color'],
                    ]);
                }
            }
        }
        unset($book);
    }

    // ── Stats ───────────────────────────────────────────────────────────
    $statsStmt = $pdo->query("
        SELECT
            COUNT(*) AS total,
            SUM(CASE WHEN status = 'available' THEN 1 ELSE 0 END) AS available,
            SUM(CASE WHEN status = 'checked_out' THEN 1 ELSE 0 END) AS checked_out,
            COUNT(DISTINCT classification_id) AS classifications
        FROM library_books
    ");
    $stats = $statsStmt->fetch(PDO::FETCH_ASSOC);

    echo json_encode([
        'success'    => true,
        'data'       => $books,
        'total'      => $total,
        'page'       => $page,
        'limit'      => $limit,
        'totalPages' => max(1, ceil($total / $limit)),
        'stats'      => [
            'total'       => (int) ($stats['total'] ?? 0),
            'available'   => (int) ($stats['available'] ?? 0),
            'checked_out' => (int) ($stats['checked_out'] ?? 0),
            'classifications'  => (int) ($stats['classifications'] ?? 0),
        ],
    ]);
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Server error: ' . $e->getMessage(),
    ]);
}
