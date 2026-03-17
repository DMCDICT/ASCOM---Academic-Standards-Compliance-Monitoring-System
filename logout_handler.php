<?php
// logout_handler.php
// Centralized logout handler that properly updates user status

// Check if Super Admin is logged in first
if (isset($_SESSION['super_admin_logged_in']) && $_SESSION['super_admin_logged_in'] === true) {
    require_once 'super_admin_session_config.php';
} else {
    require_once 'session_config.php';
}

// Start session if not already started
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// Function to update user's logout status
function updateUserLogoutStatus($employee_no) {
    if (!$employee_no || $employee_no === 'SUPER_ADMIN') {
        return; // Skip for super admin or missing employee number
    }
    
    try {
        require_once 'super_admin-mis/includes/db_connection.php';
        
        // Set online_status to 'offline' and update last_logout
        try {
            $updateQuery = "UPDATE users SET online_status = 'offline', last_logout = NOW() WHERE employee_no = ?";
            $updateStmt = $conn->prepare($updateQuery);
            $updateStmt->bind_param("s", $employee_no);
            $updateStmt->execute();
            $updateStmt->close();
            error_log("User logout status updated for: " . $employee_no);
        } catch (Exception $e) {
            // If new columns don't exist, just log the logout
            error_log("New columns not found during logout: " . $e->getMessage());
        }
        
        $conn->close();
    } catch (Exception $e) {
        error_log("Error updating user logout status: " . $e->getMessage());
    }
}

// Get user info before clearing session
$employee_no = $_SESSION['employee_no'] ?? null;
$user_role = $_SESSION['user_role'] ?? '';
$username = $_SESSION['username'] ?? '';

// Update user's logout status
if ($employee_no) {
    updateUserLogoutStatus($employee_no);
}

// Log the logout event
$logoutInfo = [
    'employee_no' => $employee_no,
    'username' => $username,
    'user_role' => $user_role,
    'logout_time' => date('Y-m-d H:i:s'),
    'logout_reason' => isset($_GET['reason']) ? $_GET['reason'] : 'manual_logout'
];

// Log to file for debugging
file_put_contents('logout_log.txt', json_encode($logoutInfo) . PHP_EOL, FILE_APPEND);

// Clear session data
$_SESSION = array();

// Destroy the session
session_destroy();

// Determine redirect URL based on user role
$redirectUrl = 'user_login.php'; // Default redirect

if ($user_role === 'super_admin' || isset($_SESSION['super_admin_logged_in'])) {
    $redirectUrl = 'index.php'; // Super Admin login page
}

// If this is an AJAX request, return JSON response
if (isset($_SERVER['HTTP_X_REQUESTED_WITH']) && $_SERVER['HTTP_X_REQUESTED_WITH'] === 'XMLHttpRequest') {
    header('Content-Type: application/json');
    echo json_encode([
        'success' => true,
        'message' => 'Logged out successfully',
        'redirect' => $redirectUrl
    ]);
    exit;
}

// Regular redirect
header("Location: " . $redirectUrl);
exit();
?> 