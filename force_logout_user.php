<?php
// force_logout_user.php
// Force logout a specific user by employee number

require_once 'session_config.php';

// Start session if not already started
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// Only Super Admin can force logout users
if (!isset($_SESSION['super_admin_logged_in']) || $_SESSION['super_admin_logged_in'] !== true) {
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit;
}

// Get employee number from request
$employee_no = $_POST['employee_no'] ?? $_GET['employee_no'] ?? null;

if (!$employee_no) {
    echo json_encode(['success' => false, 'message' => 'Employee number required']);
    exit;
}

try {
    require_once 'super_admin-mis/includes/db_connection.php';
    
    // Force update user status to offline
    $updateQuery = "UPDATE users SET online_status = 'offline', last_logout = NOW() WHERE employee_no = ? AND employee_no != 'SUPER_ADMIN'";
    $updateStmt = $conn->prepare($updateQuery);
    $updateStmt->bind_param("s", $employee_no);
    $updateStmt->execute();
    
    if ($updateStmt->affected_rows > 0) {
        echo json_encode([
            'success' => true, 
            'message' => "User $employee_no force logged out successfully",
            'employee_no' => $employee_no
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