<?php
// remove_user_role.php
// API endpoint for Super Admin to remove roles from users

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

if (!$input || !isset($input['role_id'])) {
    echo json_encode(['success' => false, 'message' => 'Missing required parameters']);
    exit;
}

$roleId = intval($input['role_id']);

try {
    // Get role info before deletion
    $roleInfoQuery = $conn->prepare("
        SELECT ur.id, ur.user_id, ur.role_name, ur.department_id, 
               u.first_name, u.last_name, d.department_name
        FROM user_roles ur
        JOIN users u ON ur.user_id = u.id
        LEFT JOIN departments d ON ur.department_id = d.id
        WHERE ur.id = ? AND ur.is_active = 1
    ");
    $roleInfoQuery->bind_param("i", $roleId);
    $roleInfoQuery->execute();
    $roleInfoResult = $roleInfoQuery->get_result();
    
    if ($roleInfoResult->num_rows === 0) {
        echo json_encode(['success' => false, 'message' => 'Role assignment not found']);
        exit;
    }
    
    $roleInfo = $roleInfoResult->fetch_assoc();
    
    // Check if this is the user's only role
    $roleCountQuery = $conn->prepare("SELECT COUNT(*) as role_count FROM user_roles WHERE user_id = ? AND is_active = 1");
    $roleCountQuery->bind_param("i", $roleInfo['user_id']);
    $roleCountQuery->execute();
    $roleCountResult = $roleCountQuery->get_result();
    $roleCount = $roleCountResult->fetch_assoc()['role_count'];
    
    if ($roleCount <= 1) {
        echo json_encode(['success' => false, 'message' => 'Cannot remove the only role from a user']);
        exit;
    }
    
    // Soft delete the role (set is_active = 0)
    $removeQuery = "UPDATE user_roles SET is_active = 0 WHERE id = ?";
    $removeStmt = $conn->prepare($removeQuery);
    $removeStmt->bind_param("i", $roleId);
    
    if ($removeStmt->execute()) {
        echo json_encode([
            'success' => true,
            'message' => 'Role removed successfully',
            'data' => [
                'role_id' => $roleId,
                'user_id' => $roleInfo['user_id'],
                'user_name' => $roleInfo['first_name'] . ' ' . $roleInfo['last_name'],
                'role_name' => $roleInfo['role_name'],
                'department_name' => $roleInfo['department_name'],
                'removed_at' => date('Y-m-d H:i:s')
            ]
        ]);
    } else {
        echo json_encode(['success' => false, 'message' => 'Failed to remove role: ' . $removeStmt->error]);
    }
    
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => 'Database error occurred']);
}

$conn->close();
?>
