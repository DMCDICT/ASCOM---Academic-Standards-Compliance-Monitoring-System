<?php
// get_dashboard_stats.php
// Fetches dashboard statistics for the librarian dashboard

header('Content-Type: application/json');

try {
    // Include database connection
    require_once dirname(__FILE__) . '/includes/db_connection.php';
    
    // Initialize response array
    $stats = array();
    
    // 1. Total Books - Count only compliant book references (within 5 years)
    $totalBooksQuery = "
        SELECT COUNT(*) as total_books
        FROM book_references 
        WHERE (YEAR(CURDATE()) - publication_year) < 5
    ";
    $totalBooksStmt = $pdo->prepare($totalBooksQuery);
    $totalBooksStmt->execute();
    $totalBooksResult = $totalBooksStmt->fetch(PDO::FETCH_ASSOC);
    $stats['total_books'] = (int)$totalBooksResult['total_books'];
    
    // 2. Compliant Courses - Count courses with 5 or more compliant books
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
    $stats['compliant_courses'] = (int)$compliantCoursesResult['compliant_courses'];
    
    // 3. Non-Compliant Courses - Count courses with less than 5 compliant books
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
    $stats['non_compliant_courses'] = (int)$nonCompliantCoursesResult['non_compliant_courses'];
    
    // Return success response
    echo json_encode([
        'success' => true,
        'data' => $stats
    ]);
    
} catch (Exception $e) {
    // Return error response
    echo json_encode([
        'success' => false,
        'message' => 'Database error: ' . $e->getMessage()
    ]);
}
?>
