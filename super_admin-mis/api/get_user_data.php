<?php
// get_user_data.php
// API endpoint to fetch user data for editing

header('Content-Type: application/json');

// Include database connection
require_once __DIR__ . '/../includes/db_connection.php';

if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Method not allowed']);
    exit;
}

try {
    // Validate required parameters
    if (!isset($_GET['employee_no']) || empty($_GET['employee_no'])) {
        throw new Exception("Employee number is required");
    }

    $employee_no = trim($_GET['employee_no']);

    // Fetch user data with role and department information
    $query = "SELECT u.*, d.department_name, d.department_code 
              FROM users u 
              LEFT JOIN departments d ON u.department_id = d.id 
              WHERE u.employee_no = ?";
    
    $stmt = $conn->prepare($query);
    $stmt->bind_param("s", $employee_no);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows === 0) {
        throw new Exception("User not found");
    }
    
    $userData = $result->fetch_assoc();
    $stmt->close();

    // Fetch the current password (plain text)
    $passwordQuery = "SELECT password FROM users WHERE employee_no = ? LIMIT 1";
    $passwordStmt = $conn->prepare($passwordQuery);
    $passwordStmt->bind_param("s", $employee_no);
    $passwordStmt->execute();
    $passwordResult = $passwordStmt->get_result();
    $userPassword = '';
    if ($passwordResult && $passwordResult->num_rows > 0) {
        $row = $passwordResult->fetch_assoc();
        $userPassword = $row['password'];
    }
    $passwordStmt->close();
    $userData['current_password'] = $userPassword;

    // Return user data
    echo json_encode([
        'success' => true,
        'data' => $userData
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