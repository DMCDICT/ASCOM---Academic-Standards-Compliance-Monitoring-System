<?php
// assign_user_role.php
// API endpoint for Super Admin to assign roles to users

require_once '../includes/db_connection.php';

header('Content-Type: application/json');

// Check if user is Super Admin
session_start();
if (!isset($_SESSION['is_authenticated']) || !$_SESSION['is_authenticated'] || 
    !isset($_SESSION['user_role']) || $_SESSION['user_role'] !== 'super_admin') {
    echo json_encode(['success' => false, 'message' => 'Unauthorized access']);
    exit;
}

// Only allow POST requests
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Invalid request method']);
    exit;
}

// Get JSON input
$input = json_decode(file_get_contents('php://input'), true);

if (!$input || !isset($input['user_id']) || !isset($input['role_name'])) {
    echo json_encode(['success' => false, 'message' => 'Missing required parameters']);
    exit;
}

$userId = intval($input['user_id']);
$roleName = trim($input['role_name']);
$departmentId = isset($input['department_id']) ? intval($input['department_id']) : null;
$assignedBy = $_SESSION['user_first_name'] . ' ' . $_SESSION['user_last_name'];

try {
    // Validate user exists
    $userCheck = $conn->prepare("SELECT id, first_name, last_name FROM users WHERE id = ? AND is_active = 1");
    $userCheck->bind_param("i", $userId);
    $userCheck->execute();
    $userResult = $userCheck->get_result();
    
    if ($userResult->num_rows === 0) {
        echo json_encode(['success' => false, 'message' => 'User not found']);
        exit;
    }
    
    // Validate role name
    $validRoles = ['teacher', 'dean', 'librarian', 'quality_assurance'];
    if (!in_array($roleName, $validRoles)) {
        echo json_encode(['success' => false, 'message' => 'Invalid role name']);
        exit;
    }
    
    // Check if role already exists for this user
    $existingCheck = $conn->prepare("SELECT id FROM user_roles WHERE user_id = ? AND role_name = ? AND is_active = 1");
    $existingCheck->bind_param("is", $userId, $roleName);
    $existingCheck->execute();
    $existingResult = $existingCheck->get_result();
    
    if ($existingResult->num_rows > 0) {
        echo json_encode(['success' => false, 'message' => 'User already has this role']);
        exit;
    }
    
    // Insert new role assignment
    $insertQuery = "INSERT INTO user_roles (user_id, role_name, department_id, assigned_by) VALUES (?, ?, ?, ?)";
    $insertStmt = $conn->prepare($insertQuery);
    $insertStmt->bind_param("isis", $userId, $roleName, $departmentId, $assignedBy);
    
    if ($insertStmt->execute()) {
        $roleId = $conn->insert_id;
        
        // Get user info for response
        $userInfo = $userResult->fetch_assoc();
        
        echo json_encode([
            'success' => true,
            'message' => 'Role assigned successfully',
            'data' => [
                'role_id' => $roleId,
                'user_id' => $userId,
                'user_name' => $userInfo['first_name'] . ' ' . $userInfo['last_name'],
                'role_name' => $roleName,
                'department_id' => $departmentId,
                'assigned_by' => $assignedBy,
                'assigned_at' => date('Y-m-d H:i:s')
            ]
        ]);
    } else {
        echo json_encode(['success' => false, 'message' => 'Failed to assign role: ' . $insertStmt->error]);
    }
    
} catch (Exception $e) {
    error_log("Error in assign_user_role.php: " . $e->getMessage());
    echo json_encode(['success' => false, 'message' => 'Database error occurred']);
}

$conn->close();
?>
