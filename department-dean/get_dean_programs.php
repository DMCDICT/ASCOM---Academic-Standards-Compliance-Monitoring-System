<?php
// get_dean_programs.php - FIXED VERSION
// Direct database connection to programs table - NO SESSION DEPENDENCY

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
    
    // Get ALL programs from the programs table - NO SESSION FILTERING
    $programsQuery = "SELECT id, program_name, program_code FROM programs ORDER BY program_name ASC";
    $programsStmt = $pdo->prepare($programsQuery);
    $programsStmt->execute();
    $programs = $programsStmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo json_encode([
        'success' => true,
        'programs' => $programs
    ]);
    
} catch (Exception $e) {
    error_log("Error in get_dean_programs.php: " . $e->getMessage());
    echo json_encode([
        'success' => false,
        'message' => 'Database error occurred.',
        'error' => $e->getMessage()
    ]);
}
?>