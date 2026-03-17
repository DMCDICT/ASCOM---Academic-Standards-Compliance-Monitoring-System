<?php
// get_user_roles.php
// API endpoint to get all roles a user has (teacher and/or dean)

require_once '../includes/db_connection.php';

header('Content-Type: application/json');

// Check if user ID is provided
if (!isset($_GET['user_id']) || empty($_GET['user_id'])) {
    echo json_encode([
        'success' => false,
        'message' => 'User ID is required'
    ]);
    exit;
}

$userId = $_GET['user_id'];

try {
    // Get user's basic info
    $userQuery = "
        SELECT 
            u.id,
            u.first_name,
            u.last_name,
            u.title,
            u.employee_no,
            u.role_id,
            u.department_id,
            d.department_code,
            d.department_name
        FROM 
            users u
        LEFT JOIN 
            departments d ON u.department_id = d.id
        WHERE 
            u.id = ? 
            AND u.is_active = 1
    ";
    
    $userStmt = $conn->prepare($userQuery);
    $userStmt->bind_param("i", $userId);
    $userStmt->execute();
    $userResult = $userStmt->get_result();
    
    if ($userResult->num_rows === 0) {
        echo json_encode([
            'success' => false,
            'message' => 'User not found'
        ]);
        exit;
    }
    
    $userRow = $userResult->fetch_assoc();
    
    // Check if user is a dean of any department
    $deanQuery = "
        SELECT 
            d.id,
            d.department_code,
            d.department_name
        FROM 
            departments d
        WHERE 
            d.dean_user_id = ?
    ";
    
    $deanStmt = $conn->prepare($deanQuery);
    $deanStmt->bind_param("i", $userId);
    $deanStmt->execute();
    $deanResult = $deanStmt->get_result();
    
    $roles = [];
    
    // Add teacher role if user is a teacher
    if ($userRow['role_id'] == 4) {
        $roles[] = [
            'type' => 'teacher',
            'department_code' => $userRow['department_code'],
            'department_name' => $userRow['department_name']
        ];
    }
    
    // Add dean role if user is a dean
    if ($deanResult->num_rows > 0) {
        while ($deanRow = $deanResult->fetch_assoc()) {
            $roles[] = [
                'type' => 'dean',
                'department_code' => $deanRow['department_code'],
                'department_name' => $deanRow['department_name']
            ];
        }
    }
    
    // Format display name with title
    $displayName = $userRow['title'] ? $userRow['title'] . ' ' . $userRow['first_name'] . ' ' . $userRow['last_name'] : $userRow['first_name'] . ' ' . $userRow['last_name'];
    
    echo json_encode([
        'success' => true,
        'user' => [
            'id' => $userRow['id'],
            'display_name' => $displayName,
            'first_name' => $userRow['first_name'],
            'last_name' => $userRow['last_name'],
            'title' => $userRow['title'],
            'employee_no' => $userRow['employee_no'],
            'department_id' => $userRow['department_id'],
            'department_code' => $userRow['department_code'],
            'department_name' => $userRow['department_name']
        ],
        'roles' => $roles,
        'has_multiple_roles' => count($roles) > 1
    ]);
    
} catch (Exception $e) {
    error_log("Error in get_user_roles.php: " . $e->getMessage());
    echo json_encode([
        'success' => false,
        'message' => 'Database error occurred'
    ]);
}
?>
