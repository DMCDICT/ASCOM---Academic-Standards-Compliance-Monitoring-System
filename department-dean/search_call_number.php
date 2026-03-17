<?php
// search_call_number.php
// Searches for books by call number in library_books table

header('Content-Type: application/json');
require_once dirname(__FILE__) . '/../session_config.php';
require_once dirname(__FILE__) . '/includes/db_connection.php';

// Start session if not already started
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

$response = ['success' => false, 'books' => []];

// Check authentication
if (!isset($_SESSION['dean_logged_in']) || $_SESSION['dean_logged_in'] !== true) {
    $response['message'] = 'Unauthorized access';
    echo json_encode($response);
    exit;
}

try {
    $callNumber = $_GET['call_number'] ?? '';
    
    if (empty($callNumber) || strlen($callNumber) < 2) {
        $response['success'] = true;
        echo json_encode($response);
        exit;
    }
    
    // Search in library_books table
    $searchQuery = "SELECT call_number, book_title, isbn, publisher, publication_year, edition, author 
                   FROM library_books 
                   WHERE call_number LIKE ? 
                   LIMIT 10";
    
    $stmt = $pdo->prepare($searchQuery);
    $stmt->execute(['%' . $callNumber . '%']);
    $books = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    $response['success'] = true;
    $response['books'] = $books;
    
} catch (Exception $e) {
    error_log("Error searching call number: " . $e->getMessage());
    $response['message'] = 'Search failed: ' . $e->getMessage();
}

echo json_encode($response);
?>

