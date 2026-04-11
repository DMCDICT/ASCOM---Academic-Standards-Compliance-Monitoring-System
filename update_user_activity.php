<?php

require_once 'session_config.php';

header('Content-Type: application/json');

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (!isUserActive()) {
    echo json_encode(['success' => false, 'message' => 'User not authenticated']);
    exit;
}

if (!empty($_SESSION['super_admin_logged_in']) && ($_SESSION['user_role'] ?? null) === 'super_admin') {
    echo json_encode(['success' => true, 'message' => 'Super Admin activity acknowledged']);
    exit;
}

$employeeNo = $_SESSION['employee_no'] ?? null;

if (!$employeeNo || $employeeNo === 'SUPER_ADMIN') {
    echo json_encode(['success' => false, 'message' => 'Invalid user']);
    exit;
}

try {
    $conn = ascom_get_mysqli();
    $stmt = $conn->prepare("UPDATE users SET last_activity = NOW(), online_status = 'online' WHERE employee_no = ?");
    $stmt->bind_param('s', $employeeNo);
    $stmt->execute();
    $stmt->close();

    $conn->query("UPDATE users SET online_status = 'offline' WHERE last_activity < DATE_SUB(NOW(), INTERVAL 2 MINUTE) AND online_status = 'online' AND employee_no != 'SUPER_ADMIN'");

    echo json_encode(['success' => true, 'message' => 'Activity updated']);
} catch (Throwable $e) {
    echo json_encode(['success' => false, 'message' => 'Database error']);
}
