<?php

require_once __DIR__ . '/bootstrap/database.php';

if (!function_exists('ascom_session_is_secure')) {
    function ascom_session_is_secure(): bool
    {
        if (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') {
            return true;
        }

        return (int) ($_SERVER['SERVER_PORT'] ?? 80) === 443;
    }
}

function configureExtendedSession(): void
{
    $sessionLifetime = 2 * 60 * 60;

    ini_set('session.gc_maxlifetime', (string) $sessionLifetime);
    ini_set('session.cookie_lifetime', (string) $sessionLifetime);
    ini_set('session.use_strict_mode', '1');
    ini_set('session.use_cookies', '1');
    ini_set('session.use_only_cookies', '1');
    ini_set('session.use_trans_sid', '0');
    ini_set('session.cookie_httponly', '1');
    ini_set('session.cookie_samesite', 'Lax');

    session_name('ASCOM_SESSION');
    session_set_cookie_params([
        'lifetime' => $sessionLifetime,
        'path' => '/',
        'domain' => '',
        'secure' => ascom_session_is_secure(),
        'httponly' => true,
        'samesite' => 'Lax',
    ]);
}

function isUserActive(): bool
{
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }

    if (!empty($_SESSION['super_admin_logged_in']) && ($_SESSION['user_role'] ?? null) === 'super_admin') {
        return true;
    }

    fixMissingEmployeeNo();

    return !empty($_SESSION['is_authenticated']) && !empty($_SESSION['user_id']);
}

function fixMissingEmployeeNo(): void
{
    if (isset($_SESSION['employee_no'])) {
        return;
    }

    if (!empty($_SESSION['super_admin_logged_in']) && ($_SESSION['user_role'] ?? null) === 'super_admin') {
        $_SESSION['employee_no'] = 'SUPER_ADMIN';
        return;
    }

    $userId = $_SESSION['user_id'] ?? null;
    if (!$userId) {
        return;
    }

    try {
        $conn = ascom_get_mysqli();
        $stmt = $conn->prepare('SELECT employee_no FROM users WHERE id = ?');
        $stmt->bind_param('i', $userId);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result && $row = $result->fetch_assoc()) {
            $_SESSION['employee_no'] = $row['employee_no'];
        }

        $stmt->close();
    } catch (Throwable $e) {
    }
}

function extendSession(): void
{
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }

    $lastRegen = $_SESSION['_last_session_regeneration'] ?? 0;
    if ((time() - (int) $lastRegen) >= 900) {
        session_regenerate_id(true);
        $_SESSION['_last_session_regeneration'] = time();
    }
}

function handleSessionTimeout(): void
{
    if (!isUserActive()) {
        $employeeNo = $_SESSION['employee_no'] ?? null;
        if ($employeeNo && $employeeNo !== 'SUPER_ADMIN') {
            updateUserForcedLogout($employeeNo);
        }

        $_SESSION = [];
        session_destroy();

        $currentUrl = $_SERVER['REQUEST_URI'] ?? '';
        $redirect = strpos($currentUrl, 'super_admin-mis') !== false ? '../super_admin_login.php' : 'user_login.php';
        header("Location: {$redirect}");
        exit();
    }
}

function updateUserForcedLogout(?string $employeeNo): void
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

if (session_status() === PHP_SESSION_NONE) {
    configureExtendedSession();
}
