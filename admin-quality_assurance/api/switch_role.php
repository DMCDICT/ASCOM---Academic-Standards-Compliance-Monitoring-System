<?php
// Include session configuration
require_once dirname(__FILE__) . '/../../session_config.php';
session_start();
header('Content-Type: application/json');

// Get real user data from database
require_once dirname(__FILE__) . '/../../super_admin-mis/includes/db_connection.php';

// Get user ID from session - try multiple session variables
$userId = $_SESSION['user_id'] ?? $_SESSION['id'] ?? null;

// Debug: Log what we found

if (!$userId) {
    echo json_encode(['success' => false, 'error' => 'User ID not found in session']);
    exit();
}

// Get user information with department from database
$stmt = $conn->prepare("
    SELECT u.id, u.first_name, u.last_name, u.title,
           d.department_code, d.department_name, d.color_code
    FROM users u
    LEFT JOIN departments d ON u.department_id = d.id
    WHERE u.id = ?
");

$stmt->bind_param("i", $userId);
$stmt->execute();
$result = $stmt->get_result();
$user = $result->fetch_assoc();

// Debug: Log user data
if ($user) {
}

if ($user) {
    // Set session with real user data from database
    $_SESSION['admin_qa_logged_in'] = false;
    $_SESSION['teacher_logged_in'] = true;
    $_SESSION['user_id'] = $user['id'];
    $_SESSION['user_first_name'] = $user['first_name'];
    $_SESSION['user_last_name'] = $user['last_name'];
    $_SESSION['user_title'] = $user['title'];
    $_SESSION['selected_role'] = [
        'role' => 'teacher',
        'department_code' => $user['department_code'],
        'department_name' => $user['department_name'],
        'department_color' => $user['color_code']
    ];
    
    // Debug: Log what we're setting
    
    // Ensure session is written
    session_write_close();
}

echo json_encode([
    'success' => true,
    'message' => 'Successfully switched to Teacher role',
    'redirect_url' => '../teachers/content.php'
]);
?>
