<?php
// get_school_years.php - FIXED VERSION
// Direct database connection to school_years table

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET');
header('Access-Control-Allow-Headers: Content-Type');

try {
    // Direct database connection - NO SESSION DEPENDENCY
    $host = 'localhost';
    $dbname = 'ascom_db';
    $username = 'root';
    $password = '';
    
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    // Query to fetch school years from the database
    $query = "SELECT id, school_year_label, year_start, year_end, status FROM school_years ORDER BY year_start DESC LIMIT 10";
    $stmt = $pdo->prepare($query);
    $stmt->execute();
    $schoolYears = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Format the response
    $formattedYears = [];
    foreach ($schoolYears as $year) {
        $formattedYears[] = [
            'id' => $year['id'],
            'school_year' => $year['school_year_label'],
            'status' => $year['status']
        ];
    }
    
    echo json_encode([
        'success' => true,
        'school_years' => $formattedYears
    ]);
    
} catch (Exception $e) {
    error_log("Error fetching school years: " . $e->getMessage());
    echo json_encode([
        'success' => false,
        'error' => 'Failed to fetch school years',
        'message' => $e->getMessage()
    ]);
}
?>
