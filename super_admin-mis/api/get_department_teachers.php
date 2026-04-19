<?php
// get_department_teachers.php
// API endpoint to fetch potential deans for a specific department
// Only returns users who have the Dean role (role_id = 2)

header('Content-Type: application/json');
require_once __DIR__ . '/../includes/db_connection.php';

$response = ['success' => false, 'teachers' => [], 'message' => ''];

// Support both department_id and dept_code
$department_id = null;

if (isset($_GET['department_id'])) {
    $department_id = (int)$_GET['department_id'];
} elseif (isset($_GET['dept_code'])) {
    // Get department ID from department code
    $deptCode = $_GET['dept_code'];
    $deptQuery = "SELECT id FROM departments WHERE department_code = ?";
    $deptStmt = $conn->prepare($deptQuery);
    $deptStmt->bind_param("s", $deptCode);
    $deptStmt->execute();
    $deptResult = $deptStmt->get_result();
    
    if ($deptResult->num_rows > 0) {
        $deptRow = $deptResult->fetch_assoc();
        $department_id = $deptRow['id'];
    }
    $deptStmt->close();
}

if (!$department_id || $department_id <= 0) {
    $response['message'] = 'Invalid department';
    echo json_encode($response);
    $conn->close();
    exit;
}

// Get users with Dean role (role_id = 2) for this department
// Check BOTH user_roles table AND users.role_id for compatibility
$query = "SELECT DISTINCT
    u.id, 
    u.employee_no, 
    u.first_name, 
    u.last_name, 
    u.title,
    u.institutional_email,
    u.email,
    u.department_id
FROM users u
WHERE u.department_id = ? 
    AND u.is_active = 1
    AND (
        -- Check user_roles table (new multi-role system)
        u.id IN (SELECT user_id FROM user_roles WHERE role_id = 2)
        OR
        -- Check direct role_id in users table (legacy compatibility)
        u.role_id = 2
    )
ORDER BY u.first_name ASC";

$stmt = $conn->prepare($query);
$stmt->bind_param("i", $department_id);
$stmt->execute();
$result = $stmt->get_result();

while ($row = $result->fetch_assoc()) {
    // Build full name
    $fullName = trim(($row['title'] ?? '') . ' ' . $row['first_name'] . ' ' . $row['last_name']);
    $row['full_name'] = $fullName;
    $row['display_name'] = $row['first_name'] . ' ' . $row['last_name'];
    $row['roles'] = 'dean'; // They have dean role
    $response['teachers'][] = $row;
}

$response['success'] = true;
$stmt->close();

echo json_encode($response);
$conn->close();