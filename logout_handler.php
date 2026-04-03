<?php

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
        error_log('Error updating user logout status: ' . $e->getMessage());
    }
}

$employeeNo = $_SESSION['employee_no'] ?? null;
$userRole = $_SESSION['user_role'] ?? '';

updateUserLogoutStatus($employeeNo);

$_SESSION = [];
session_destroy();

$redirectUrl = $userRole === 'super_admin' ? 'index.php' : 'user_login.php';

if (
    isset($_SERVER['HTTP_X_REQUESTED_WITH']) &&
    $_SERVER['HTTP_X_REQUESTED_WITH'] === 'XMLHttpRequest'
) {
    header('Content-Type: application/json');
    echo json_encode([
        'success' => true,
        'message' => 'Logged out successfully',
        'redirect' => $redirectUrl,
    ]);
    exit;
}

header("Location: {$redirectUrl}");
exit();
