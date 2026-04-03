<?php

require_once 'session_config.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (!empty($_SESSION['super_admin_logged_in']) && ($_SESSION['user_role'] ?? null) === 'super_admin') {
    http_response_code(200);
    echo 'Super Admin session preserved';
    exit;
}

$employeeNo = $_SESSION['employee_no'] ?? ($_POST['employee_no'] ?? $_GET['employee_no'] ?? null);

if ($employeeNo && $employeeNo !== 'SUPER_ADMIN') {
    updateUserForcedLogout($employeeNo);
}

$_SESSION = [];
session_destroy();

http_response_code(200);
echo 'Logged out successfully due to tab close';
