<?php
// super_admin_logout_handler.php
// Dedicated logout handler for Super Admin ONLY

// Disable output buffering issues
ob_start();

require_once 'super_admin_session_config.php';

// Start session if not already started
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// Log the Super Admin logout event (optional logging - disabled to avoid permission issues)
// $logoutInfo = [
//     'employee_no' => $_SESSION['employee_no'] ?? 'SUPER_ADMIN',
//     'username' => $_SESSION['username'] ?? 'super_admin@ascom.edu.ph',
//     'user_role' => $_SESSION['user_role'] ?? 'super_admin',
//     'logout_time' => date('Y-m-d H:i:s'),
//     'logout_reason' => 'super_admin_manual_logout'
// ];

// Clear session data
$_SESSION = array();

// Destroy the session
session_destroy();

// Clear Super Admin session cookie
if (isset($_COOKIE['ASCOM_SUPER_ADMIN_SESSION'])) {
    setcookie('ASCOM_SUPER_ADMIN_SESSION', '', time() - 3600, '/');
}

// Clear any output and redirect
ob_end_clean();

// Redirect to Super Admin login page
header("Location: super_admin_login.php");
exit();
?> 