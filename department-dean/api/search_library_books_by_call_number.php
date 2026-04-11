<?php
// search_library_books_by_call_number.php
// API endpoint for searching library books by call number

header('Content-Type: application/json');
require_once dirname(__FILE__) . '/../includes/db_connection.php';

$response = ['success' => false, 'message' => '', 'books' => []];

try {
    $callNumber = $_GET['call_number'] ?? '';
    $limit = min(intval($_GET['limit'] ?? 10), 20); // Max 20 results
    
    if (empty($callNumber)) {
        $response['message'] = 'Call number is required';
        echo json_encode($response);
        exit;
    }
    
    // Search by call number (partial match)
    $searchQuery = "SELECT id, call_number, title, authors, copyright_year, publisher, edition, location, subject_category 
                   FROM library_books 
                   WHERE call_number LIKE ?
                   ORDER BY call_number ASC 
                   LIMIT ?";
    
    $likeQuery = '%' . $callNumber . '%';
    $stmt = $pdo->prepare($searchQuery);
    $stmt->execute([$likeQuery, $limit]);
    $books = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    $response['success'] = true;
    $response['books'] = $books;
    $response['count'] = count($books);
    
} catch (Exception $e) {
    $response['message'] = 'Search failed: ' . $e->getMessage();
}

echo json_encode($response);
?>

