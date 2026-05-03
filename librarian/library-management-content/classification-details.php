<?php
// classification-details.php for Librarian
// This page displays books grouped by classification based on call number

// Get URL parameters
$classificationRange = $_GET['range'] ?? '';
$classificationName = $_GET['name'] ?? 'Classification Details';

// Database connection
require_once dirname(__FILE__) . '/../includes/db_connection.php';

// Parse the range (e.g., "000-099" -> min: 0, max: 99)
$rangeParts = explode('-', $classificationRange);
$minRange = isset($rangeParts[0]) ? intval($rangeParts[0]) : 0;
$maxRange = isset($rangeParts[1]) ? intval($rangeParts[1]) : 999;

// Fetch books based on call number classification (supports legacy book_references + new library_books)
$bookReferences = [];
try {
    $allBooks = [];

    // Legacy source: book_references (course-linked)
    $hasBookReferences = (bool) $pdo->query("SHOW TABLES LIKE 'book_references'")->fetchColumn();
    if ($hasBookReferences) {
        $legacyQuery = "
            SELECT
                br.id,
                br.book_title,
                br.author,
                br.isbn,
                br.publisher,
                br.publication_year,
                br.call_number,
                br.no_of_copies,
                br.edition,
                br.location,
                br.processing_status,
                c.course_code,
                c.course_title,
                'book_reference' AS source_type
            FROM book_references br
            LEFT JOIN courses c ON br.course_id = c.id
            WHERE br.call_number IS NOT NULL
              AND br.call_number != ''
            ORDER BY br.call_number ASC, br.book_title ASC
        ";
        $legacyStmt = $pdo->prepare($legacyQuery);
        $legacyStmt->execute();
        $allBooks = array_merge($allBooks, $legacyStmt->fetchAll(PDO::FETCH_ASSOC));
    }

    // New source: library_books (classification-linked)
    // Note: classification range is derived from call_number, not classification_id,
    // so we include all call-numbered books and filter in PHP for consistency.
    $hasLibraryBooks = (bool) $pdo->query("SHOW TABLES LIKE 'library_books'")->fetchColumn();
    if ($hasLibraryBooks) {
        $libraryQuery = "
            SELECT
                lb.id,
                lb.book_title,
                lb.author,
                lb.isbn,
                lb.publisher,
                lb.publication_year,
                lb.call_number,
                lb.no_of_copies,
                lb.edition,
                lb.location,
                lb.status AS processing_status,
                NULL AS course_code,
                NULL AS course_title,
                'library_book' AS source_type
            FROM library_books lb
            WHERE lb.call_number IS NOT NULL
              AND lb.call_number != ''
            ORDER BY lb.call_number ASC, lb.book_title ASC
        ";
        $libraryStmt = $pdo->prepare($libraryQuery);
        $libraryStmt->execute();
        $allBooks = array_merge($allBooks, $libraryStmt->fetchAll(PDO::FETCH_ASSOC));
    }

    // Filter books based on call number classification in PHP (tolerant parsing)
    $bookReferences = array_values(array_filter($allBooks, function($book) use ($minRange, $maxRange) {
        $callNumber = trim((string)($book['call_number'] ?? ''));
        if ($callNumber === '') {
            return false;
        }

        // Extract first 1–3 digit block, allowing optional prefixes like "CS 001.1"
        // Examples matched:
        // - "004.6782 D569"
        // - "4.6782 D569"
        // - "CS 001.1"
        // - "DDC 050"
        if (!preg_match('/^\D*(\d{1,3})/', $callNumber, $matches)) {
            return false;
        }

        $firstDigits = intval($matches[1]);
        $paddedDigits = str_pad($firstDigits, 3, '0', STR_PAD_LEFT);
        $number = intval($paddedDigits);
        return $number >= $minRange && $number <= $maxRange;
    }));
    
} catch (Exception $e) {
    $bookReferences = [];
}

$totalBooks = count($bookReferences);
?>

<div class="shelf-view-container">
    <div class="back-navigation" style="margin-bottom: 24px;">
        <button class="btn-primary" onclick="window.history.back()" style="background: transparent; color: #0C4B34; border: 1px solid rgba(12, 75, 52, 0.2); padding: 8px 16px;">
            <i data-lucide="arrow-left" style="width: 18px; height: 18px;"></i>
            Back to Dashboard
        </button>
    </div>

    <div class="shelf-header-premium">
        <div class="section-header">
            <div class="label-bar"></div>
            <div>
                <h3><?php echo htmlspecialchars($classificationName); ?></h3>
                <p>Browsing books in classification range: <strong><?php echo htmlspecialchars($classificationRange); ?></strong></p>
            </div>
        </div>

        <div class="shelf-stats-row">
            <div class="shelf-stat-card">
                <div class="shelf-stat-icon">
                    <i data-lucide="book-open"></i>
                </div>
                <div class="shelf-stat-info">
                    <span class="shelf-stat-label">Total Books</span>
                    <span class="shelf-stat-value"><?php echo $totalBooks; ?></span>
                </div>
            </div>
            <div class="shelf-stat-card">
                <div class="shelf-stat-icon">
                    <i data-lucide="layers"></i>
                </div>
                <div class="shelf-stat-info">
                    <span class="shelf-stat-label">Range</span>
                    <span class="shelf-stat-value"><?php echo htmlspecialchars($classificationRange); ?></span>
                </div>
            </div>
            <div class="shelf-stat-card">
                <div class="shelf-stat-icon" style="background: rgba(21, 101, 192, 0.08); color: #1565C0;">
                    <i data-lucide="map-pin"></i>
                </div>
                <div class="shelf-stat-info">
                    <span class="shelf-stat-label">System</span>
                    <span class="shelf-stat-value">DDC</span>
                </div>
            </div>
        </div>
    </div>

    <div class="shelves-container">
        <?php if (!empty($bookReferences)): ?>
            <?php 
            // Chunk books into groups of 5 for visual "shelves"
            $shelves = array_chunk($bookReferences, 5);
            foreach ($shelves as $shelfIndex => $booksInShelf): 
            ?>
                <div class="shelf-row">
                    <div class="shelf-table-wrap">
                        <table class="shelf-table">
                            <thead>
                                <tr>
                                    <th>Title</th>
                                    <th>Author</th>
                                    <th>Call Number</th>
                                    <th>Location</th>
                                    <th>Year</th>
                                    <th>Source</th>
                                    <th>Action</th>
                                </tr>
                            </thead>
                            <tbody>
		                        <?php foreach ($booksInShelf as $book): ?>
		                            <?php
		                                $isCourseLinked = !empty($book['course_code']);
		                                $courseUrl = $isCourseLinked
		                                    ? "content.php?page=course-details&course_code=" . urlencode($book['course_code'])
		                                    : null;
                                    $rowClass = $isCourseLinked ? 'shelf-table-row is-clickable' : 'shelf-table-row is-static';
                                    $rowLabel = ($book['book_title'] ?? 'Untitled') . ' by ' . ($book['author'] ?? 'Unknown Author');
		                            ?>
                                    <tr
                                        class="<?php echo $rowClass; ?>"
                                        <?php if ($courseUrl) echo 'role="link" tabindex="0" aria-label="' . htmlspecialchars($rowLabel) . '" onclick="window.location.href=' . htmlspecialchars(json_encode($courseUrl)) . '" onkeydown="if(event.key===\'Enter\'||event.key===\' \'){event.preventDefault();window.location.href=' . htmlspecialchars(json_encode($courseUrl)) . ';}"'; ?>
                                    >
                                        <td class="book-title-cell" data-label="Title">
                                            <div class="book-title-main"><?php echo htmlspecialchars($book['book_title'] ?? 'Untitled'); ?></div>
                                            <div class="book-title-sub">Catalog entry</div>
                                        </td>
                                        <td data-label="Author"><?php echo htmlspecialchars($book['author'] ?? 'Unknown Author'); ?></td>
                                        <td data-label="Call Number">
                                            <?php if (!empty($book['call_number'])): ?>
                                                <span class="table-chip">
                                                    <i data-lucide="hash" style="width: 10px; height: 10px;"></i>
                                                    <?php echo htmlspecialchars($book['call_number']); ?>
                                                </span>
                                            <?php else: ?>
                                                <span class="table-muted">No call number</span>
                                            <?php endif; ?>
                                        </td>
                                        <td data-label="Location">
                                            <?php if (!empty($book['location'])): ?>
                                                <span class="table-chip alt">
                                                    <i data-lucide="map-pin" style="width: 10px; height: 10px;"></i>
                                                    <?php echo htmlspecialchars($book['location']); ?>
                                                </span>
                                            <?php else: ?>
                                                <span class="table-muted">Unassigned</span>
                                            <?php endif; ?>
                                        </td>
                                        <td data-label="Year"><?php echo htmlspecialchars($book['publication_year'] ?? 'N/A'); ?></td>
                                        <td data-label="Source">
                                            <span class="source-pill <?php echo $isCourseLinked ? 'course' : 'library'; ?>">
                                                <?php echo $isCourseLinked ? 'Course-linked' : 'Library'; ?>
                                            </span>
                                        </td>
                                        <td class="action-cell" data-label="Action">
                                            <?php if ($isCourseLinked): ?>
                                                <span class="action-link">
                                                    Open
                                                    <i data-lucide="chevron-right" style="width: 14px; height: 14px;"></i>
                                                </span>
                                            <?php else: ?>
                                                <span class="table-muted">View only</span>
                                            <?php endif; ?>
                                        </td>
                                    </tr>
		                        <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php else: ?>
            <div class="empty-state" style="padding: 100px 20px;">
                <i data-lucide="library" style="width: 64px; height: 64px; margin-bottom: 24px; color: rgba(12, 75, 52, 0.2);"></i>
                <h3 style="font-size: 20px; color: #111827; margin-bottom: 8px;">Empty Shelf</h3>
                <p>No books have been cataloged in this classification range yet.</p>
            </div>
        <?php endif; ?>
    </div>
</div>

<script>
    // Initialize Lucide icons
    if (typeof lucide !== 'undefined') {
        lucide.createIcons();
    }
</script>
