<?php
// get_user_info.php
// Provide current user information to JavaScript

require_once 'session_config.php';

// Start session if not already started
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// Get user information
$employee_no = $_SESSION['employee_no'] ?? null;
$username = $_SESSION['username'] ?? null;
$user_role = $_SESSION['user_role'] ?? null;
$is_super_admin = isset($_SESSION['super_admin_logged_in']) && $_SESSION['super_admin_logged_in'] === true;

// Check if user is logged in
$is_logged_in = false;
if ($is_super_admin) {
    $is_logged_in = true;
} elseif (isset($_SESSION['dean_logged_in']) && $_SESSION['dean_logged_in'] === true) {
    $is_logged_in = true;
} elseif (isset($_SESSION['teacher_logged_in']) && $_SESSION['teacher_logged_in'] === true) {
    $is_logged_in = true;
} elseif (isset($_SESSION['librarian_logged_in']) && $_SESSION['librarian_logged_in'] === true) {
    $is_logged_in = true;
} elseif (isset($_SESSION['admin_qa_logged_in']) && $_SESSION['admin_qa_logged_in'] === true) {
    $is_logged_in = true;
}

// Return user information as JSON
header('Content-Type: application/json');

if ($is_logged_in && $employee_no) {
    echo json_encode([
        'success' => true,
        'employee_no' => $employee_no,
        'username' => $username,
        'user_role' => $user_role,
        'is_super_admin' => $is_super_admin,
        'is_logged_in' => $is_logged_in
    ]);
} else {
    echo json_encode([
        'success' => false,
        'message' => 'User not logged in or employee number not found',
        'is_logged_in' => $is_logged_in,
        'employee_no' => $employee_no
    ]);
}
?> 