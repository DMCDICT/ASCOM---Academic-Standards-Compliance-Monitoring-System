<?php

require_once __DIR__ . '/session_config.php';

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

function isSuperAdminAuthenticated(): bool
{
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }

    return !empty($_SESSION['super_admin_logged_in']) && ($_SESSION['user_role'] ?? null) === 'super_admin';
}

function secureSuperAdminSession(): void
{
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }

    $_SESSION['super_admin_logged_in'] = true;
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
    header('Location: ../index.php');
    exit();
}

configureSuperAdminSession();
