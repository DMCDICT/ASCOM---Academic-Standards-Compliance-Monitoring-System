<?php
// process_delete_user.php
// Handle delete user operation

header('Content-Type: application/json');

// Include database connection
require_once __DIR__ . '/includes/db_connection.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Method not allowed']);
    exit;
}

try {
    // Get JSON input
    $input = json_decode(file_get_contents('php://input'), true);
    
    // Validate required fields
    if (!isset($input['employee_no']) || empty($input['employee_no'])) {
        throw new Exception("Employee number is required");
    }

    $employee_no = trim($input['employee_no']);

    // Check if user exists
    $checkUserQuery = "SELECT id, first_name, last_name, institutional_email FROM users WHERE employee_no = ?";
    $checkUserStmt = $conn->prepare($checkUserQuery);
    $checkUserStmt->bind_param("s", $employee_no);
    $checkUserStmt->execute();
    $checkUserResult = $checkUserStmt->get_result();
    
    if ($checkUserResult->num_rows === 0) {
        throw new Exception("User not found");
    }
    
    $userData = $checkUserResult->fetch_assoc();
    $checkUserStmt->close();

    // Delete the user
    $deleteQuery = "DELETE FROM users WHERE employee_no = ?";
    $deleteStmt = $conn->prepare($deleteQuery);
    $deleteStmt->bind_param("s", $employee_no);
    
    if (!$deleteStmt->execute()) {
        throw new Exception("Failed to delete user: " . $conn->error);
    }

    if ($deleteStmt->affected_rows === 0) {
        throw new Exception("No user was deleted");
    }

    $deleteStmt->close();

    // Return success response
    echo json_encode([
        'success' => true,
        'message' => 'User account deleted successfully',
        'user_name' => $userData['first_name'] . ' ' . $userData['last_name']
    ]);

} catch (Exception $e) {
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}

$conn->close();
?> 