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

// Fetch book references based on call number classification
$bookReferences = [];
try {
    // First, get all books with call numbers (we'll filter in PHP for accuracy)
    $query = "
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
            c.course_title
        FROM book_references br
        LEFT JOIN courses c ON br.course_id = c.id
        WHERE br.call_number IS NOT NULL 
        AND br.call_number != ''
        ORDER BY br.call_number ASC, br.book_title ASC
    ";
    
    $stmt = $pdo->prepare($query);
    $stmt->execute();
    $allBooks = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Filter books based on call number classification in PHP
    $bookReferences = array_filter($allBooks, function($book) use ($minRange, $maxRange) {
        if (empty($book['call_number'])) {
            return false;
        }
        
        // Extract first 3 digits from call number
        // Handle formats like "004.6782 D569", "004 D569", "4.6782 D569", "4 D569"
        preg_match('/^(\d{1,3})/', trim($book['call_number']), $matches);
        if (isset($matches[1])) {
            $firstDigits = intval($matches[1]);
            // Pad to 3 digits for comparison (e.g., 4 -> 004, 04 -> 004)
            $paddedDigits = str_pad($firstDigits, 3, '0', STR_PAD_LEFT);
            $number = intval($paddedDigits);
            return $number >= $minRange && $number <= $maxRange;
        }
        
        return false;
    });
    
    // Re-index array after filtering
    $bookReferences = array_values($bookReferences);
    
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
                    <div class="shelf-books-grid">
                        <?php foreach ($booksInShelf as $book): ?>
                            <div class="book-spine-card" onclick="window.location.href='content.php?page=course-details&course_code=<?php echo urlencode($book['course_code'] ?? ''); ?>'">
                                <div>
                                    <h3 class="book-spine-title"><?php echo htmlspecialchars($book['book_title'] ?? 'Untitled'); ?></h3>
                                    <p class="book-spine-author"><?php echo htmlspecialchars($book['author'] ?? 'Unknown Author'); ?></p>
                                    
                                    <div class="book-spine-meta">
                                        <?php if (!empty($book['call_number'])): ?>
                                            <span class="book-spine-badge">
                                                <i data-lucide="hash" style="width: 10px; height: 10px; display: inline-block; margin-right: 2px;"></i>
                                                <?php echo htmlspecialchars($book['call_number']); ?>
                                            </span>
                                        <?php endif; ?>
                                        
                                        <?php if (!empty($book['location'])): ?>
                                            <span class="book-spine-badge" style="background: rgba(21, 101, 192, 0.06); color: #1565C0; border-color: rgba(21, 101, 192, 0.1);">
                                                <i data-lucide="map-pin" style="width: 10px; height: 10px; display: inline-block; margin-right: 2px;"></i>
                                                <?php echo htmlspecialchars($book['location']); ?>
                                            </span>
                                        <?php endif; ?>
                                    </div>
                                </div>
                                
                                <div class="book-spine-footer">
                                    <span class="book-spine-year"><?php echo htmlspecialchars($book['publication_year'] ?? 'N/A'); ?></span>
                                    <div style="color: #0C4B34;">
                                        <i data-lucide="chevron-right" style="width: 16px; height: 16px;"></i>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
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

