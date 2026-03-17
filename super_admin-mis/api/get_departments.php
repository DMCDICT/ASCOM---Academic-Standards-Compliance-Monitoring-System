<?php
// get_departments.php
// API endpoint to fetch departments from the database

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET');
header('Access-Control-Allow-Headers: Content-Type');

// Include database connection
require_once '../includes/db_connection.php';

try {
    // Check if database connection is available
    if (!isset($conn) || !$conn instanceof mysqli || $conn->connect_error) {
        throw new Exception("Database connection failed");
    }
    
    // Fetch departments from the database
    $query = "SELECT id, department_code, department_name FROM departments ORDER BY department_code ASC";
    $result = $conn->query($query);
    
    if (!$result) {
        throw new Exception("Failed to fetch departments: " . $conn->error);
    }
    
    $departments = [];
    while ($row = $result->fetch_assoc()) {
        $departments[] = [
            'id' => $row['id'],
            'code' => $row['department_code'],
            'name' => $row['department_name']
        ];
    }
    
    // Return JSON response
    echo json_encode([
        'success' => true,
        'departments' => $departments
    ]);
    
} catch (Exception $e) {
    // Return error response
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'error' => $e->getMessage(),
        'departments' => []
    ]);
}

$conn->close();
?> 