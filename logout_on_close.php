<?php
// logout_on_close.php
// Handle logout when tab/window is closed - performs full logout

require_once 'session_config.php';
require_once 'super_admin_session_config.php';

// Start session if not already started
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// Super Admin should never be logged out automatically - they manage the system
if (isset($_SESSION['super_admin_logged_in']) && $_SESSION['super_admin_logged_in'] === true) {
    // Log the event but don't logout Super Admin
    error_log("Super Admin tab/window closed - session preserved at: " . date('Y-m-d H:i:s'));
    
    // Send a simple response
    http_response_code(200);
    echo "Super Admin session preserved";
    exit;
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
            error_log("User tab close logout status updated for: " . $employee_no);
        } catch (Exception $e) {
            // If new columns don't exist, just log the logout
            error_log("New columns not found during tab close logout: " . $e->getMessage());
        }
        
        $conn->close();
    } catch (Exception $e) {
        error_log("Error updating user tab close logout status: " . $e->getMessage());
    }
}

// Get user info before clearing session
$employee_no = $_SESSION['employee_no'] ?? null;
$user_role = $_SESSION['user_role'] ?? '';
$username = $_SESSION['username'] ?? '';

// Also check for employee number in request data
$request_employee_no = $_POST['employee_no'] ?? $_GET['employee_no'] ?? null;
if ($request_employee_no && (!$employee_no || $employee_no === 'UNKNOWN')) {
    $employee_no = $request_employee_no;
}

// Update user's logout status
if ($employee_no && $employee_no !== 'SUPER_ADMIN') {
    updateUserLogoutStatus($employee_no);
}

// Log the logout event
$logoutInfo = [
    'employee_no' => $employee_no,
    'username' => $username,
    'user_role' => $user_role,
    'logout_time' => date('Y-m-d H:i:s'),
    'logout_reason' => 'tab_close_full_logout'
];

// Log to file for debugging
file_put_contents('logout_log.txt', json_encode($logoutInfo) . PHP_EOL, FILE_APPEND);

// Clear session data (FULL LOGOUT)
$_SESSION = array();

// Destroy the session (FULL LOGOUT)
session_destroy();

// Send a simple response
http_response_code(200);
echo "Logged out successfully due to tab close";
?> 