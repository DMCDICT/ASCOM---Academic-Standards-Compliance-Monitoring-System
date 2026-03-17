<?php
// Suppress error output to prevent HTML in JSON response
error_reporting(0);
ini_set('display_errors', 0);

header('Content-Type: application/json');

require_once '../includes/db_connection.php';

if ($conn->connect_error) {
    echo json_encode(['success' => false, 'message' => 'Database connection failed']);
    exit;
}

try {
    // Get current librarian assignments
    $query = "
        SELECT 
            u.id,
            u.first_name,
            u.last_name,
            u.institutional_email,
            u.employee_no,
            ur.assigned_at,
            ur.assigned_by
        FROM user_roles ur
        JOIN users u ON ur.user_id = u.id
        WHERE ur.role_name = 'librarian' 
        AND ur.is_active = 1
        ORDER BY ur.assigned_at DESC
    ";
    
    $stmt = $conn->prepare($query);
    $stmt->execute();
    $result = $stmt->get_result();
    
    $librarians = [];
    while ($row = $result->fetch_assoc()) {
        $librarians[] = [
            'id' => $row['id'],
            'name' => $row['first_name'] . ' ' . $row['last_name'],
            'email' => $row['institutional_email'],
            'employee_no' => $row['employee_no'],
            'assigned_at' => $row['assigned_at'],
            'assigned_by' => $row['assigned_by']
        ];
    }
    
    echo json_encode([
        'success' => true,
        'librarians' => $librarians,
        'count' => count($librarians)
    ]);
    
} catch (Exception $e) {
    error_log("Error in get_current_librarian_access.php: " . $e->getMessage());
    echo json_encode([
        'success' => false, 
        'message' => 'Failed to fetch current librarian access: ' . $e->getMessage()
    ]);
}

$conn->close();
?>
