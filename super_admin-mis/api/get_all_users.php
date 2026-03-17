<?php
// get_all_users.php
// API endpoint to fetch all users data for real-time status updates

header('Content-Type: application/json');
header('Cache-Control: no-cache, no-store, must-revalidate');
header('Pragma: no-cache');
header('Expires: 0');

// Include database connection
require_once __DIR__ . '/../includes/db_connection.php';

try {
    // Fetch all users with their latest activity data
    $fetchUsersQuery = "SELECT 
        u.employee_no, 
        u.first_name, 
        u.middle_name, 
        u.last_name, 
        u.institutional_email, 
        u.mobile_no, 
        u.role_id, 
        u.department_id, 
        u.is_active, 
        u.last_activity,
        u.online_status,
        u.last_login,
        u.last_logout,
        r.role as role_name,
        d.department_name,
        d.department_code
        FROM users u 
        LEFT JOIN roles r ON u.role_id = r.id 
        LEFT JOIN departments d ON u.department_id = d.id 
        ORDER BY u.id DESC";
    
    $fetchUsersResult = $conn->query($fetchUsersQuery);
    
    if (!$fetchUsersResult) {
        throw new Exception("Failed to fetch users: " . $conn->error);
    }
    
    $users = [];
    while ($row = $fetchUsersResult->fetch_assoc()) {
        // Get user ID for role lookup
        $userIdQuery = "SELECT id FROM users WHERE employee_no = ?";
        $userIdStmt = $conn->prepare($userIdQuery);
        $userIdStmt->bind_param("s", $row['employee_no']);
        $userIdStmt->execute();
        $userIdResult = $userIdStmt->get_result();
        
        $roles = [];
        if ($userIdResult->num_rows > 0) {
            $userData = $userIdResult->fetch_assoc();
            $userId = $userData['id'];
            
            // Fetch all roles from user_roles table
            $rolesQuery = "SELECT role_name FROM user_roles WHERE user_id = ? AND is_active = 1";
            $rolesStmt = $conn->prepare($rolesQuery);
            $rolesStmt->bind_param("i", $userId);
            $rolesStmt->execute();
            $rolesResult = $rolesStmt->get_result();
            
            while ($roleRow = $rolesResult->fetch_assoc()) {
                $roles[] = $roleRow['role_name'];
            }
            
            // Check if user is also a Department Dean (from departments table)
            $deanQuery = "SELECT id FROM departments WHERE dean_user_id = ?";
            $deanStmt = $conn->prepare($deanQuery);
            $deanStmt->bind_param("i", $userId);
            $deanStmt->execute();
            $deanResult = $deanStmt->get_result();
            
            if ($deanResult->num_rows > 0) {
                $roles[] = 'department_dean';
            }
            
            // If no roles found in user_roles table, use the primary role
            if (empty($roles)) {
                $roles = [$row['role_name']];
            }
        } else {
            // Fallback to primary role if user not found
            $roles = [$row['role_name']];
        }
        
        // Format the data for frontend consumption
        $users[] = [
            'employee_no' => $row['employee_no'],
            'first_name' => $row['first_name'],
            'middle_name' => $row['middle_name'],
            'last_name' => $row['last_name'],
            'institutional_email' => $row['institutional_email'],
            'mobile_no' => $row['mobile_no'],
            'role_id' => $row['role_id'],
            'department_id' => $row['department_id'],
            'is_active' => $row['is_active'],
            'last_activity' => $row['last_activity'],
            'online_status' => $row['online_status'],
            'last_login' => $row['last_login'],
            'last_logout' => $row['last_logout'],
            'role_name' => $row['role_name'],
            'roles' => $roles,
            'department_name' => $row['department_name'],
            'department_code' => $row['department_code']
        ];
    }
    
    $fetchUsersResult->free();
    
    echo json_encode([
        'success' => true,
        'users' => $users,
        'total_count' => count($users)
    ]);
    
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}

$conn->close();
?> 