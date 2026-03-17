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

// Get the role type from the request
$roleType = $_GET['role_type'] ?? '';

if (empty($roleType)) {
    echo json_encode(['success' => false, 'message' => 'Role type is required']);
    exit;
}

try {
    // Get users who don't have ANY special roles (librarian, QA, or dean)
    $query = "
        SELECT 
            u.id,
            u.first_name,
            u.last_name,
            u.institutional_email,
            u.employee_no,
            u.role_id,
            d.department_name
        FROM users u
        LEFT JOIN departments d ON u.department_id = d.id
        WHERE u.is_active = 1
        AND u.role_id = 4  -- Using role_id = 4 since that's what exists in the database
        AND u.id NOT IN (
            -- Exclude users who are already department deans
            SELECT DISTINCT dean_user_id 
            FROM departments 
            WHERE dean_user_id IS NOT NULL
        )
        AND u.id NOT IN (
            -- Exclude users who already have librarian role
            SELECT DISTINCT user_id 
            FROM user_roles 
            WHERE role_name = 'librarian' AND is_active = 1
        )
        AND u.id NOT IN (
            -- Exclude users who already have QA role
            SELECT DISTINCT user_id 
            FROM user_roles 
            WHERE role_name = 'quality_assurance' AND is_active = 1
        )
        ORDER BY u.first_name, u.last_name
    ";
    
    $stmt = $conn->prepare($query);
    $stmt->execute();
    $result = $stmt->get_result();
    
    $users = [];
    while ($row = $result->fetch_assoc()) {
        $users[] = [
            'id' => $row['id'],
            'name' => $row['first_name'] . ' ' . $row['last_name'],
            'email' => $row['institutional_email'],
            'employee_no' => $row['employee_no'],
            'role' => 'Teacher', // We'll show them as teachers for assignment purposes
            'role_id' => $row['role_id'],
            'department' => $row['department_name'] ?? 'N/A'
        ];
    }
    
    echo json_encode([
        'success' => true,
        'users' => $users,
        'count' => count($users)
    ]);
    
} catch (Exception $e) {
    error_log("Error in get_available_users.php: " . $e->getMessage());
    echo json_encode([
        'success' => false, 
        'message' => 'Failed to fetch available users: ' . $e->getMessage()
    ]);
}

$conn->close();
?>
