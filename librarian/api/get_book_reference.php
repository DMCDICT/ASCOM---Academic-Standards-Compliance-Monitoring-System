<?php
// get_book_reference.php
// API endpoint to fetch a single book reference by ID

header('Content-Type: application/json');
require_once dirname(__FILE__) . '/../includes/db_connection.php';

$response = ['success' => false, 'data' => null, 'message' => ''];

try {
    // Get book ID from query parameter
    $bookId = $_GET['id'] ?? null;
    
    if (!$bookId) {
        throw new Exception('Book ID is required');
    }
    
    // Fetch book reference by ID
    $query = "
        SELECT 
            br.id,
            br.book_title,
            br.author,
            br.isbn,
            br.publisher,
            br.publication_year,
            br.edition,
            br.location,
            br.call_number,
            br.no_of_copies,
            br.processing_status,
            br.status_reason,
            br.course_id,
            c.course_code,
            c.course_title
        FROM book_references br
        LEFT JOIN courses c ON br.course_id = c.id
        WHERE br.id = ?
        LIMIT 1
    ";
    
    $stmt = $pdo->prepare($query);
    $stmt->execute([$bookId]);
    $book = $stmt->fetch(PDO::FETCH_ASSOC);
    
    // Convert no_of_copies to integer if it exists
    if (isset($book['no_of_copies'])) {
        $book['no_of_copies'] = (int)$book['no_of_copies'];
    }
    
    if (!$book) {
        throw new Exception('Book reference not found');
    }
    
    $response['success'] = true;
    $response['data'] = $book;
    $response['message'] = 'Book reference fetched successfully';
    
} catch (Exception $e) {
    error_log("Error fetching book reference: " . $e->getMessage());
    $response['message'] = 'Failed to fetch book reference: ' . $e->getMessage();
}

echo json_encode($response);
exit;

