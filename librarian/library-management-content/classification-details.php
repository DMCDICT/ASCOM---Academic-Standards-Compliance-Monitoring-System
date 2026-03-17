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
    error_log("Error fetching classification books: " . $e->getMessage());
    $bookReferences = [];
}

$totalBooks = count($bookReferences);
?>

<div class="back-navigation">
    <button class="back-button" onclick="window.history.back()">
        <img src="../src/assets/icons/go-back-icon.png" alt="Back">
        Back to Dashboard
    </button>
</div>

<div class="course-details-container">
    <div class="course-header">
        <div class="course-title-section">
            <h1><?php echo htmlspecialchars($classificationName); ?></h1>
            <div class="course-meta">
                <span class="classification-badge" style="background-color: #4CAF50; color: white; padding: 8px 16px; border-radius: 8px; font-weight: 600; font-size: 14px;">
                    <?php echo htmlspecialchars($classificationRange); ?>
                </span>
            </div>
        </div>
        <div class="course-stats">
            <div class="stat-item">
                <span class="stat-label">Total Books</span>
                <span class="stat-value"><?php echo $totalBooks; ?></span>
            </div>
        </div>
    </div>

    <div class="course-info-section">
        <div class="section-title">
            <h2>Book References</h2>
            <div class="section-line"></div>
        </div>

        <?php if (!empty($bookReferences)): ?>
            <div class="book-references-grid">
                <?php foreach ($bookReferences as $book): ?>
                    <div class="book-reference-card">
                        <div class="book-header">
                            <div class="book-title-section">
                                <h3 class="book-title"><?php echo htmlspecialchars($book['book_title'] ?? 'N/A'); ?></h3>
                                <?php if (!empty($book['author'])): ?>
                                    <p class="book-author"><?php echo htmlspecialchars($book['author']); ?></p>
                                <?php endif; ?>
                            </div>
                            <div class="book-meta">
                                <?php if (!empty($book['call_number'])): ?>
                                    <div class="call-number-badge" style="background: #e3f2fd; color: #1976d2; padding: 6px 12px; border-radius: 6px; font-weight: 600; font-size: 13px;">
                                        <?php echo htmlspecialchars($book['call_number']); ?>
                                    </div>
                                <?php endif; ?>
                            </div>
                        </div>
                        
                        <div class="book-details">
                            <?php if (!empty($book['course_code'])): ?>
                                <div class="book-detail-item">
                                    <span class="detail-label">Course:</span>
                                    <span class="detail-value"><?php echo htmlspecialchars($book['course_code']); ?> - <?php echo htmlspecialchars($book['course_title'] ?? ''); ?></span>
                                </div>
                            <?php endif; ?>
                            
                            <?php if (!empty($book['isbn'])): ?>
                                <div class="book-detail-item">
                                    <span class="detail-label">ISBN:</span>
                                    <span class="detail-value"><?php echo htmlspecialchars($book['isbn']); ?></span>
                                </div>
                            <?php endif; ?>
                            
                            <?php if (!empty($book['publisher'])): ?>
                                <div class="book-detail-item">
                                    <span class="detail-label">Publisher:</span>
                                    <span class="detail-value"><?php echo htmlspecialchars($book['publisher']); ?></span>
                                </div>
                            <?php endif; ?>
                            
                            <?php if (!empty($book['publication_year'])): ?>
                                <div class="book-detail-item">
                                    <span class="detail-label">Year:</span>
                                    <span class="detail-value"><?php echo htmlspecialchars($book['publication_year']); ?></span>
                                </div>
                            <?php endif; ?>
                            
                            <?php if (!empty($book['edition'])): ?>
                                <div class="book-detail-item">
                                    <span class="detail-label">Edition:</span>
                                    <span class="detail-value"><?php echo htmlspecialchars($book['edition']); ?></span>
                                </div>
                            <?php endif; ?>
                            
                            <?php if (!empty($book['no_of_copies'])): ?>
                                <div class="book-detail-item">
                                    <span class="detail-label">Copies:</span>
                                    <span class="detail-value"><?php echo htmlspecialchars($book['no_of_copies']); ?></span>
                                </div>
                            <?php endif; ?>
                            
                            <?php if (!empty($book['location'])): ?>
                                <div class="book-detail-item">
                                    <span class="detail-label">Location:</span>
                                    <span class="detail-value"><?php echo htmlspecialchars($book['location']); ?></span>
                                </div>
                            <?php endif; ?>
                        </div>
                        
                        <?php if (!empty($book['course_code'])): ?>
                            <div class="book-card-footer">
                                <a href="content.php?page=course-details&course_code=<?php echo urlencode($book['course_code']); ?>" class="view-course-btn">
                                    View Course
                                </a>
                            </div>
                        <?php endif; ?>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php else: ?>
            <div style="text-align: center; padding: 60px 20px; color: #666;">
                <div style="font-size: 48px; margin-bottom: 16px;">📚</div>
                <h3 style="font-family: 'TT Interphases', sans-serif; font-size: 18px; color: #333; margin-bottom: 8px;">No Books Found</h3>
                <p style="font-family: 'TT Interphases', sans-serif; font-size: 14px; color: #666;">
                    No books have been cataloged with call numbers in the range <?php echo htmlspecialchars($classificationRange); ?>.
                </p>
            </div>
        <?php endif; ?>
    </div>
</div>

<style>
.course-details-container {
    max-width: 1200px;
    margin: 0 auto;
    padding: 24px;
}

.back-navigation {
    margin-bottom: 20px;
}

.back-button {
    display: inline-flex;
    align-items: center;
    gap: 8px;
    padding: 10px 16px;
    background: #1976d2;
    border: none;
    color: white;
    font-size: 16px;
    font-weight: 600;
    cursor: pointer;
    border-radius: 8px;
    text-decoration: none;
    transition: background-color 0.2s;
    font-family: 'TT Interphases', sans-serif;
}

.back-button:hover {
    background: #1565c0;
}

.back-button img {
    width: 20px;
    height: 20px;
}

.course-header {
    display: flex;
    justify-content: space-between;
    align-items: flex-start;
    margin-bottom: 32px;
    padding-bottom: 24px;
    border-bottom: 2px solid #e0e0e0;
}

.course-title-section h1 {
    font-family: 'TT Interphases', sans-serif;
    font-size: 32px;
    color: #333;
    margin: 0 0 12px 0;
}

.course-meta {
    display: flex;
    gap: 12px;
    align-items: center;
}

.course-stats {
    display: flex;
    gap: 24px;
}

.stat-item {
    text-align: center;
}

.stat-label {
    display: block;
    font-family: 'TT Interphases', sans-serif;
    font-size: 12px;
    color: #666;
    margin-bottom: 4px;
}

.stat-value {
    display: block;
    font-family: 'TT Interphases', sans-serif;
    font-size: 24px;
    font-weight: 600;
    color: #333;
}

.section-title {
    margin-bottom: 24px;
}

.section-title h2 {
    font-family: 'TT Interphases', sans-serif;
    font-size: 24px;
    color: #333;
    margin: 0 0 8px 0;
}

.section-line {
    height: 3px;
    width: 60px;
    background: #4CAF50;
    border-radius: 2px;
}

.book-references-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(350px, 1fr));
    gap: 20px;
    align-items: start;
}

.book-reference-card {
    background: white;
    border: 1px solid #e0e0e0;
    border-radius: 12px;
    padding: 20px;
    transition: all 0.2s ease;
    box-shadow: 0 2px 4px rgba(0,0,0,0.05);
    display: flex;
    flex-direction: column;
    height: auto;
}

.book-reference-card:hover {
    box-shadow: 0 4px 12px rgba(0,0,0,0.1);
    transform: translateY(-2px);
}

.book-header {
    display: flex;
    justify-content: space-between;
    align-items: flex-start;
    margin-bottom: 16px;
}

.book-title-section {
    flex: 1;
}

.book-title {
    font-family: 'TT Interphases', sans-serif;
    font-size: 18px;
    font-weight: 600;
    color: #333;
    margin: 0 0 6px 0;
    line-height: 1.4;
}

.book-author {
    font-family: 'TT Interphases', sans-serif;
    font-size: 14px;
    color: #666;
    margin: 0;
}

.book-meta {
    margin-left: 12px;
}

.book-details {
    display: flex;
    flex-direction: column;
    gap: 8px;
    flex: 1;
}

.book-detail-item {
    display: flex;
    font-family: 'TT Interphases', sans-serif;
    font-size: 14px;
}

.detail-label {
    font-weight: 600;
    color: #666;
    min-width: 80px;
}

.detail-value {
    color: #333;
}

.call-number-badge {
    font-family: 'TT Interphases', sans-serif;
}

.book-card-footer {
    margin-top: auto;
    padding-top: 16px;
    border-top: 1px solid #e0e0e0;
    display: flex;
    justify-content: flex-end;
}

.view-course-btn {
    display: inline-block;
    padding: 8px 16px;
    background: #1976d2;
    color: white;
    text-decoration: none;
    border-radius: 6px;
    font-family: 'TT Interphases', sans-serif;
    font-size: 14px;
    font-weight: 600;
    transition: all 0.2s ease;
    text-align: center;
}

.view-course-btn:hover {
    background: #1565c0;
    transform: translateY(-1px);
    box-shadow: 0 2px 8px rgba(25, 118, 210, 0.3);
}

@media (max-width: 768px) {
    .book-references-grid {
        grid-template-columns: 1fr;
    }
    
    .course-header {
        flex-direction: column;
        gap: 16px;
    }
}
</style>

