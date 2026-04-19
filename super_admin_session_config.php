<?php

if (!function_exists('ascom_session_is_secure')) {
    function ascom_session_is_secure(): bool
    {
        if (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') {
            return true;
        }

        return (int) ($_SERVER['SERVER_PORT'] ?? 80) === 443;
    }
}

function configureSuperAdminSession(): void
{
    $sessionLifetime = 365 * 24 * 60 * 60;

    ini_set('session.gc_maxlifetime', (string) $sessionLifetime);
    ini_set('session.cookie_lifetime', (string) $sessionLifetime);
    ini_set('session.use_strict_mode', '1');
    ini_set('session.use_cookies', '1');
    ini_set('session.use_only_cookies', '1');
    ini_set('session.use_trans_sid', '0');
    ini_set('session.cookie_httponly', '1');
    ini_set('session.cookie_samesite', 'Lax');

    session_name('ASCOM_SUPER_ADMIN_SESSION');
    session_set_cookie_params([
        'lifetime' => $sessionLifetime,
        'path' => '/',
        'domain' => '',
        'secure' => ascom_session_is_secure(),
        'httponly' => true,
        'samesite' => 'Lax',
    ]);
}

// Definition of configureSuperAdminSession() remains above

require_once __DIR__ . '/session_config.php';

function isSuperAdminAuthenticated(): bool
{
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }

    return !empty($_SESSION['super_admin_logged_in']) && 
           ($_SESSION['user_role'] ?? null) === 'super_admin' &&
           !empty($_SESSION['super_admin_session']);
}

function secureSuperAdminSession(): void
{
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }

    $_SESSION['super_admin_logged_in'] = true;
    $_SESSION['super_admin_session'] = true;
    $_SESSION['user_role'] = 'super_admin';
    $_SESSION['employee_no'] = 'SUPER_ADMIN';
    $_SESSION['is_authenticated'] = true;
    $_SESSION['_last_session_regeneration'] = time();

    if (!isset($_SESSION['username'])) {
        $_SESSION['username'] = 'super_admin@ascom.edu.ph';
    }

    if (!isset($_SESSION['user_id'])) {
        $_SESSION['user_id'] = 0;
    }
}

function extendSuperAdminSession(): bool
{
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }

    $_SESSION['_last_session_regeneration'] = time();
    return true;
}

function handleSuperAdminAuthFailure(): void
{
    $_SESSION = [];
    session_destroy();
    $currentUrl = $_SERVER['REQUEST_URI'] ?? '';
    $redirect = strpos($currentUrl, 'super_admin-mis') !== false ? '../super_admin_login.php' : 'super_admin_login.php';
    header("Location: {$redirect}");
    exit();
}

// Call configuration at the end to ensure it overrides session_config.php settings
configureSuperAdminSession();
