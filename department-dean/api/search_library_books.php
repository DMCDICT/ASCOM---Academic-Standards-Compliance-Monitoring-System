<?php
// search_library_books.php
// API endpoint for searching library books catalog

header('Content-Type: application/json');
require_once dirname(__FILE__) . '/../includes/db_connection.php';

$response = ['success' => false, 'message' => '', 'books' => []];

try {
    $query = $_GET['q'] ?? '';
    $category = $_GET['category'] ?? '';
    $limit = min(intval($_GET['limit'] ?? 20), 50); // Max 50 results
    
    if (empty($query)) {
        $response['message'] = 'Search query is required';
        echo json_encode($response);
        exit;
    }
    
    // Build search query
    $searchQuery = "SELECT id, title, authors, isbn, publisher, copyright_year, edition, call_number, location, subject_category 
                   FROM library_books 
                   WHERE MATCH(title, authors, description, keywords) AGAINST (? IN NATURAL LANGUAGE MODE)";
    
    $params = [$query];
    
    if (!empty($category)) {
        $searchQuery .= " AND subject_category = ?";
        $params[] = $category;
    }
    
    $searchQuery .= " ORDER BY 
                     CASE 
                         WHEN title LIKE ? THEN 1
                         WHEN authors LIKE ? THEN 2
                         ELSE 3
                     END, title ASC 
                     LIMIT ?";
    
    $likeQuery = '%' . $query . '%';
    $params[] = $likeQuery;
    $params[] = $likeQuery;
    $params[] = $limit;
    
    $stmt = $pdo->prepare($searchQuery);
    $stmt->execute($params);
    $books = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Format response
    foreach ($books as &$book) {
        $book['display_title'] = $book['title'];
        $book['display_authors'] = $book['authors'];
        $book['display_info'] = $book['publisher'] . ' (' . $book['copyright_year'] . ')';
        $book['display_location'] = $book['call_number'] . ' - ' . $book['location'];
    }
    
    $response['success'] = true;
    $response['books'] = $books;
    $response['count'] = count($books);
    
} catch (Exception $e) {
    error_log("Library search error: " . $e->getMessage());
    $response['message'] = 'Search failed: ' . $e->getMessage();
}

echo json_encode($response);
?>
