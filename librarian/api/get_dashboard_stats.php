<?php
// Dashboard Stats API for Librarian
// Returns statistics for the dashboard

header('Content-Type: application/json');
error_reporting(0);
session_start();

require_once dirname(__FILE__) . '/../includes/db_connection.php';

try {
    // Total Books - Count only compliant book references (within 5 years)
    $totalBooksQuery = "
        SELECT COUNT(*) as total_books
        FROM book_references 
        WHERE (YEAR(CURDATE()) - publication_year) < 5
    ";
    $totalBooksStmt = $pdo->prepare($totalBooksQuery);
    $totalBooksStmt->execute();
    $totalBooksResult = $totalBooksStmt->fetch(PDO::FETCH_ASSOC);
    $totalBooks = (int)$totalBooksResult['total_books'];
    
    // Compliant Courses - Count courses with 5 or more compliant books
    $compliantCoursesQuery = "
        SELECT COUNT(DISTINCT c.id) as compliant_courses
        FROM courses c
        INNER JOIN (
            SELECT course_id, COUNT(*) as compliant_count
            FROM book_references 
            WHERE (YEAR(CURDATE()) - publication_year) < 5
            GROUP BY course_id
            HAVING compliant_count >= 5
        ) compliant ON c.id = compliant.course_id
    ";
    $compliantCoursesStmt = $pdo->prepare($compliantCoursesQuery);
    $compliantCoursesStmt->execute();
    $compliantCoursesResult = $compliantCoursesStmt->fetch(PDO::FETCH_ASSOC);
    $compliantCourses = (int)$compliantCoursesResult['compliant_courses'];
    
    // Non-Compliant Courses - Count courses with less than 5 compliant books
    $nonCompliantCoursesQuery = "
        SELECT COUNT(DISTINCT c.id) as non_compliant_courses
        FROM courses c
        LEFT JOIN (
            SELECT course_id, COUNT(*) as compliant_count
            FROM book_references 
            WHERE (YEAR(CURDATE()) - publication_year) < 5
            GROUP BY course_id
        ) compliant ON c.id = compliant.course_id
        WHERE COALESCE(compliant.compliant_count, 0) < 5
    ";
    $nonCompliantCoursesStmt = $pdo->prepare($nonCompliantCoursesQuery);
    $nonCompliantCoursesStmt->execute();
    $nonCompliantCoursesResult = $nonCompliantCoursesStmt->fetch(PDO::FETCH_ASSOC);
    $nonCompliantCourses = (int)$nonCompliantCoursesResult['non_compliant_courses'];
    
    // Pending Material Requests
    $pendingRequestsQuery = "
        SELECT COUNT(*) as pending_count
        FROM material_requests
        WHERE status = 'pending'
    ";
    $pendingRequestsStmt = $pdo->prepare($pendingRequestsQuery);
    $pendingRequestsStmt->execute();
    $pendingRequestsResult = $pendingRequestsStmt->fetch(PDO::FETCH_ASSOC);
    $pendingRequests = (int)$pendingRequestsResult['pending_count'];
    
    // Processing Material Requests
    $processingRequestsQuery = "
        SELECT COUNT(*) as processing_count
        FROM material_requests
        WHERE status = 'processing'
    ";
    $processingRequestsStmt = $pdo->prepare($processingRequestsQuery);
    $processingRequestsStmt->execute();
    $processingRequestsResult = $processingRequestsStmt->fetch(PDO::FETCH_ASSOC);
    $processingRequests = (int)$processingRequestsResult['processing_count'];
    
    // Total Library Books (from library_books table if exists)
    $libraryBooksQuery = "SELECT COUNT(*) as library_books FROM library_books WHERE status = 'available'";
    $libraryBooksStmt = $pdo->prepare($libraryBooksQuery);
    $libraryBooksStmt->execute();
    $libraryBooksResult = $libraryBooksStmt->fetch(PDO::FETCH_ASSOC);
    $libraryBooks = (int)$libraryBooksResult['library_books'];
    
    // Book Requests from Deans
    $bookRequestsQuery = "
        SELECT COUNT(*) as book_requests_count
        FROM book_requests
        WHERE status = 'PENDING'
    ";
    $bookRequestsStmt = $pdo->prepare($bookRequestsQuery);
    $bookRequestsStmt->execute();
    $bookRequestsResult = $bookRequestsStmt->fetch(PDO::FETCH_ASSOC);
    $bookRequests = (int)$bookRequestsResult['book_requests_count'];
    
    // Classifications count
    $classificationsQuery = "SELECT COUNT(*) as classifications_count FROM classifications";
    $classificationsStmt = $pdo->prepare($classificationsQuery);
    $classificationsStmt->execute();
    $classificationsResult = $classificationsStmt->fetch(PDO::FETCH_ASSOC);
    $classificationsCount = (int)$classificationsResult['classifications_count'];
    
    echo json_encode([
        'success' => true,
        'totalBooks' => $totalBooks,
        'compliantCourses' => $compliantCourses,
        'nonCompliantCourses' => $nonCompliantCourses,
        'pendingRequests' => $pendingRequests,
        'processingRequests' => $processingRequests,
        'libraryBooks' => $libraryBooks,
        'bookRequests' => $bookRequests,
        'classificationsCount' => $classificationsCount,
        'totalNotifications' => $pendingRequests + $bookRequests
    ]);
    
} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage(),
        'totalBooks' => 0,
        'compliantCourses' => 0,
        'nonCompliantCourses' => 0,
        'pendingRequests' => 0,
        'processingRequests' => 0,
        'libraryBooks' => 0,
        'bookRequests' => 0,
        'classificationsCount' => 0,
        'totalNotifications' => 0
    ]);
}