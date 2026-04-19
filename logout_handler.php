<?php
// Clear any output first
ob_start();

require_once 'session_config.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

function updateUserLogoutStatus(?string $employeeNo): void
{
    if (!$employeeNo || $employeeNo === 'SUPER_ADMIN') {
        return;
    }

    try {
        $conn = ascom_get_mysqli();
        $stmt = $conn->prepare("UPDATE users SET online_status = 'offline', last_logout = NOW() WHERE employee_no = ?");
        $stmt->bind_param('s', $employeeNo);
        $stmt->execute();
        $stmt->close();
    } catch (Throwable $e) {
    }
}

$employeeNo = $_SESSION['employee_no'] ?? null;
$userRole = $_SESSION['user_role'] ?? '';

updateUserLogoutStatus($employeeNo);

$_SESSION = [];
session_destroy();

// Redirect to main entry page
ob_end_clean();
header("Location: index.php");
exit();
