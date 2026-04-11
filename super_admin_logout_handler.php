<?php
// super_admin_logout_handler.php
// Dedicated logout handler for Super Admin ONLY

require_once 'super_admin_session_config.php';

// Start session if not already started
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// Log the Super Admin logout event
$logoutInfo = [
    'employee_no' => $_SESSION['employee_no'] ?? 'SUPER_ADMIN',
    'username' => $_SESSION['username'] ?? 'super_admin@ascom.edu.ph',
    'user_role' => $_SESSION['user_role'] ?? 'super_admin',
    'logout_time' => date('Y-m-d H:i:s'),
    'logout_reason' => 'super_admin_manual_logout'
];

// Log to file for debugging
file_put_contents('super_admin_logout_log.txt', json_encode($logoutInfo) . PHP_EOL, FILE_APPEND);

// Log to error log

// Clear session data
$_SESSION = array();

// Destroy the session
session_destroy();

// Clear Super Admin session cookie
if (isset($_COOKIE['ASCOM_SUPER_ADMIN_SESSION'])) {
    setcookie('ASCOM_SUPER_ADMIN_SESSION', '', time() - 3600, '/');
}

// If this is an AJAX request, return JSON response
if (isset($_SERVER['HTTP_X_REQUESTED_WITH']) && $_SERVER['HTTP_X_REQUESTED_WITH'] === 'XMLHttpRequest') {
    header('Content-Type: application/json');
    echo json_encode([
        'success' => true,
        'message' => 'Super Admin logged out successfully',
        'redirect' => 'super_admin_login.php'
    ]);
    exit;
}

// Redirect to Super Admin login page
header("Location: super_admin_login.php");
exit();
?> 