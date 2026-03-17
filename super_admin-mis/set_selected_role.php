<?php
require_once dirname(__FILE__) . '/../session_config.php';
session_start();

header('Content-Type: application/json');

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    echo json_encode([
        'success' => false,
        'message' => 'User not logged in'
    ]);
    exit;
}

// Check if this is a POST request
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode([
        'success' => false,
        'message' => 'Only POST requests are allowed'
    ]);
    exit;
}

// Get JSON input
$input = json_decode(file_get_contents('php://input'), true);

if (!isset($input['role_type']) || !isset($input['department_code'])) {
    echo json_encode([
        'success' => false,
        'message' => 'Role type and department code are required'
    ]);
    exit;
}

$roleType = strtolower($input['role_type']);
$departmentCode = $input['department_code'];

// Validate role type
if (!in_array($roleType, ['teacher', 'dean'])) {
    echo json_encode([
        'success' => false,
        'message' => 'Invalid role type'
    ]);
    exit;
}

try {
    require_once 'includes/db_connection.php';
    
    // Verify the user has this role
    $userId = $_SESSION['user_id'];
    
    if ($roleType === 'dean') {
        // Check if user is dean of this department
        $query = "
            SELECT d.id, d.department_name, d.color_code 
            FROM departments d 
            WHERE d.department_code = ? AND d.dean_user_id = ?
        ";
        $stmt = $conn->prepare($query);
        $stmt->bind_param("si", $departmentCode, $userId);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows === 0) {
            echo json_encode([
                'success' => false,
                'message' => 'User is not a dean of this department'
            ]);
            exit;
        }
        
        $deptRow = $result->fetch_assoc();
        $departmentName = $deptRow['department_name'];
        $departmentColor = $deptRow['color_code'];
        
    } else {
        // Check if user is a teacher in this department
        $query = "
            SELECT u.id, d.department_name, d.color_code 
            FROM users u 
            JOIN departments d ON u.department_id = d.id 
            WHERE u.id = ? AND u.role_id = 4 AND d.department_code = ?
        ";
        $stmt = $conn->prepare($query);
        $stmt->bind_param("is", $userId, $departmentCode);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows === 0) {
            echo json_encode([
                'success' => false,
                'message' => 'User is not a teacher in this department'
            ]);
            exit;
        }
        
        $deptRow = $result->fetch_assoc();
        $departmentName = $deptRow['department_name'];
        $departmentColor = $deptRow['color_code'];
    }
    
    // Store role information in session in a normalized structure
    $_SESSION['selected_role'] = [
        'type' => $roleType,
        'department_code' => $departmentCode,
        'department_name' => $departmentName,
        'department_color' => $departmentColor
    ];
    
    echo json_encode([
        'success' => true,
        'message' => 'Role set successfully',
        'role_type' => $roleType,
        'department_code' => $departmentCode,
        'department_name' => $departmentName
    ]);
    
} catch (Exception $e) {
    error_log("Error in set_selected_role.php: " . $e->getMessage());
    echo json_encode([
        'success' => false,
        'message' => 'Database error occurred'
    ]);
}
?>
