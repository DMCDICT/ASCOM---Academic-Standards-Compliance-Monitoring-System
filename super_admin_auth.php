<?php

require_once 'super_admin_session_config.php';
require_once 'super_admin-mis/includes/db_connection.php';
require_once 'bootstrap/auth.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: index.php');
    exit;
}

$username = isset($_POST['username']) ? trim($_POST['username']) : '';
$password = $_POST['password'] ?? '';

if ($username === '' || $password === '') {
    header('Location: index.php?error=invalid_credentials');
    exit;
}

try {
    $stmt = $conn->prepare('SELECT id, email, password, is_active FROM super_admin WHERE LOWER(email) = LOWER(?) AND is_active = 1 LIMIT 1');
    $stmt->bind_param('s', $username);
    $stmt->execute();
    $result = $stmt->get_result();
    $admin = $result ? $result->fetch_assoc() : null;
    $stmt->close();

    if (!$admin) {
        header('Location: index.php?error=invalid_credentials');
        exit;
    }

    $verified = ascom_verify_password_with_migration(
        $password,
        $admin['password'] ?? null,
        function (string $newHash) use ($conn, $admin): void {
            $update = $conn->prepare('UPDATE super_admin SET password = ? WHERE id = ?');
            $update->bind_param('si', $newHash, $admin['id']);
            $update->execute();
            $update->close();
        }
    );

    if (!$verified) {
        header('Location: index.php?error=invalid_credentials');
        exit;
    }

    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }

    session_regenerate_id(true);
    $_SESSION = [];
    $_SESSION['super_admin_logged_in'] = true;
    $_SESSION['is_authenticated'] = true;
    $_SESSION['user_role'] = 'super_admin';
    $_SESSION['user_id'] = (int) $admin['id'];
    $_SESSION['username'] = $admin['email'];
    $_SESSION['employee_no'] = 'SUPER_ADMIN';
    $_SESSION['_last_session_regeneration'] = time();

    header('Location: super_admin_successful_login.php');
    exit;
} catch (Throwable $e) {
    error_log('Super admin login failed: ' . $e->getMessage());
    header('Location: index.php?error=invalid_credentials');
    exit;
}
