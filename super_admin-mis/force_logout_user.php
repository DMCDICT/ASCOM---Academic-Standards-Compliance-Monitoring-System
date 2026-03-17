<?php
// force_logout_user.php
// Force logout a specific user by employee number

require_once '../session_config.php';

// Start session if not already started
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// Debug: Log the incoming request
$input = file_get_contents('php://input');
$post_data = json_decode($input, true);
$get_data = $_GET;
$post_vars = $_POST;

error_log("Force logout request - Input: " . $input);
error_log("Force logout request - POST: " . print_r($_POST, true));
error_log("Force logout request - GET: " . print_r($_GET, true));

// Get employee number from request (try multiple sources)
$employee_no = $post_data['employee_no'] ?? $_POST['employee_no'] ?? $_GET['employee_no'] ?? null;

if (!$employee_no) {
    echo json_encode([
        'success' => false, 
        'message' => 'Employee number required',
        'debug' => [
            'input' => $input,
            'post_data' => $post_data,
            'post_vars' => $post_vars,
            'get_vars' => $get_data
        ]
    ]);
    exit;
}

// Check if this is a Super Admin request or a self-logout request
$is_super_admin = isset($_SESSION['super_admin_logged_in']) && $_SESSION['super_admin_logged_in'] === true;
$current_user_employee_no = $_SESSION['employee_no'] ?? null;

// Allow if Super Admin OR if user is trying to logout themselves
$is_authorized = $is_super_admin || ($current_user_employee_no === $employee_no);

if (!$is_authorized) {
    echo json_encode(['success' => false, 'message' => 'Unauthorized - Only Super Admin can force logout other users, or users can logout themselves']);
    exit;
}

try {
    require_once 'includes/db_connection.php';
    
    // Force update user status to offline
    $updateQuery = "UPDATE users SET online_status = 'offline', last_logout = NOW() WHERE employee_no = ? AND employee_no != 'SUPER_ADMIN'";
    $updateStmt = $conn->prepare($updateQuery);
    $updateStmt->bind_param("s", $employee_no);
    $updateStmt->execute();
    
    if ($updateStmt->affected_rows > 0) {
        echo json_encode([
            'success' => true, 
            'message' => "User $employee_no force logged out successfully",
            'employee_no' => $employee_no,
            'requested_by' => $is_super_admin ? 'Super Admin' : 'Self'
        ]);
    } else {
        echo json_encode([
            'success' => false, 
            'message' => "User $employee_no not found or already offline"
        ]);
    }
    
    $updateStmt->close();
    $conn->close();
    
} catch (Exception $e) {
    error_log("Error in force_logout_user: " . $e->getMessage());
    echo json_encode(['success' => false, 'message' => 'Database error: ' . $e->getMessage()]);
}
?> 