<?php
// get_school_years.php
// AJAX endpoint to get school years for course addition

// Start session first
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// Database connection
$host = 'localhost';
$dbname = 'ascom_db';
$username = 'root';
$password = '';

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch(PDOException $e) {
    error_log("Database connection failed: " . $e->getMessage());
    echo json_encode([
        'success' => false,
        'message' => 'Database connection failed.'
    ]);
    exit;
}

header('Content-Type: application/json');

try {
    // Check if user is logged in
    if (!isset($_SESSION['user_id'])) {
        echo json_encode([
            'success' => false,
            'message' => 'Access denied. Please log in.'
        ]);
        exit;
    }
    
    // Get all school years
    $schoolYearsQuery = "SELECT id, school_year_label as school_year, status FROM school_years ORDER BY school_year_label DESC";
    $schoolYearsStmt = $pdo->prepare($schoolYearsQuery);
    $schoolYearsStmt->execute();
    $schoolYears = $schoolYearsStmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo json_encode([
        'success' => true,
        'school_years' => $schoolYears
    ]);
    
} catch (Exception $e) {
    error_log("Error in get_school_years.php: " . $e->getMessage());
    echo json_encode([
        'success' => false,
        'message' => 'Database error occurred.'
    ]);
}
?>
