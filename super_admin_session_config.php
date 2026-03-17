<?php
// super_admin_session_config.php
// Dedicated session configuration for Super Admin ONLY
// This file has NO timeout mechanisms and NO interference with regular users

// Set session configuration before starting any session
function configureSuperAdminSession() {
    // Set session lifetime to 1 year (in seconds) - practically unlimited
    $sessionLifetime = 365 * 24 * 60 * 60; // 1 year
    
    // Configure session settings for Super Admin
    ini_set('session.gc_maxlifetime', $sessionLifetime);
    ini_set('session.cookie_lifetime', $sessionLifetime);
    ini_set('session.use_strict_mode', 1);
    ini_set('session.use_cookies', 1);
    ini_set('session.use_only_cookies', 1);
    ini_set('session.cookie_httponly', 1);
    ini_set('session.cookie_samesite', 'Lax');
    
    // Set unique session name for Super Admin
    session_name('ASCOM_SUPER_ADMIN_SESSION');
    
    // Set cookie parameters
    session_set_cookie_params([
        'lifetime' => $sessionLifetime,
        'path' => '/',
        'domain' => '',
        'secure' => false, // Set to true if using HTTPS
        'httponly' => true,
        'samesite' => 'Lax'
    ]);
}

// Function to check if Super Admin is authenticated
function isSuperAdminAuthenticated() {
    // Check if session exists and Super Admin is logged in
    if (session_status() == PHP_SESSION_NONE) {
        session_start();
    }
    
    // Check for Super Admin session variables
    $isAuthenticated = false;
    
    if (isset($_SESSION['super_admin_logged_in']) && $_SESSION['super_admin_logged_in'] === true) {
        $isAuthenticated = true;
    } elseif (isset($_SESSION['user_role']) && $_SESSION['user_role'] === 'super_admin') {
        $isAuthenticated = true;
    } elseif (isset($_SESSION['employee_no']) && $_SESSION['employee_no'] === 'SUPER_ADMIN') {
        $isAuthenticated = true;
    }
    
    return $isAuthenticated;
}

// Function to secure Super Admin session
function secureSuperAdminSession() {
    if (session_status() == PHP_SESSION_NONE) {
        session_start();
    }
    
    // Force set all Super Admin session variables to prevent corruption
    $_SESSION['super_admin_logged_in'] = true;
    $_SESSION['user_role'] = 'super_admin';
    $_SESSION['employee_no'] = 'SUPER_ADMIN';
    
    // Add additional session variables for Super Admin
    if (!isset($_SESSION['username'])) {
        $_SESSION['username'] = 'super_admin@ascom.edu.ph';
    }
    if (!isset($_SESSION['user_id'])) {
        $_SESSION['user_id'] = 0; // Super Admin doesn't have a user ID in users table
    }
    
    // Log successful session security
    error_log("Super Admin session secured at: " . date('Y-m-d H:i:s'));
    
    // Write session immediately to prevent corruption
    session_write_close();
    session_start();
}

// Function to extend Super Admin session (no-op for Super Admin)
function extendSuperAdminSession() {
    // Super Admin sessions are unlimited - no extension needed
    error_log("Super Admin session extension requested (no-op) at: " . date('Y-m-d H:i:s'));
    return true;
}

// Function to handle Super Admin authentication failure
function handleSuperAdminAuthFailure() {
    error_log("Super Admin authentication failed at: " . date('Y-m-d H:i:s'));
    session_destroy();
    header("Location: ../index.php");
    exit();
}

// Auto-configure Super Admin session when this file is included
// Configure session name before any session operations
try {
    configureSuperAdminSession();
    
    // Only start session if not already started
    if (session_status() == PHP_SESSION_NONE) {
        session_start();
    }
} catch (Exception $e) {
    // Log error but don't display it to prevent notices
    error_log("Super Admin session configuration error: " . $e->getMessage());
}
?> 