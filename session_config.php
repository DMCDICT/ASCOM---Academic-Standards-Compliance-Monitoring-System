<?php
// session_config.php
// Configuration for extended session management

// Set session configuration before starting any session
function configureExtendedSession() {
    // Set session lifetime to 2 hours (in seconds)
    $sessionLifetime = 2 * 60 * 60; // 2 hours
    
    // Configure session settings
    ini_set('session.gc_maxlifetime', $sessionLifetime);
    ini_set('session.cookie_lifetime', $sessionLifetime);
    ini_set('session.use_strict_mode', 1);
    ini_set('session.use_cookies', 1);
    ini_set('session.use_only_cookies', 1);
    ini_set('session.cookie_httponly', 1);
    ini_set('session.cookie_samesite', 'Lax');
    
    // Set session name
    session_name('ASCOM_SESSION');
    
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

// Function to check if user is still active
function isUserActive() {
    // Check if session exists and user is logged in
    if (session_status() == PHP_SESSION_NONE) {
        session_start();
    }
    
    // Fix missing employee_no for existing sessions
    fixMissingEmployeeNo();
    
    // Check if user is authenticated
    $isAuthenticated = false;
    
    // Check for different user types
    if (isset($_SESSION['super_admin_logged_in']) && $_SESSION['super_admin_logged_in'] === true) {
        $isAuthenticated = true;
    } elseif (isset($_SESSION['dean_logged_in']) && $_SESSION['dean_logged_in'] === true) {
        $isAuthenticated = true;
    } elseif (isset($_SESSION['teacher_logged_in']) && $_SESSION['teacher_logged_in'] === true) {
        $isAuthenticated = true;
    } elseif (isset($_SESSION['librarian_logged_in']) && $_SESSION['librarian_logged_in'] === true) {
        $isAuthenticated = true;
    } elseif (isset($_SESSION['admin_qa_logged_in']) && $_SESSION['admin_qa_logged_in'] === true) {
        $isAuthenticated = true;
    }
    
    return $isAuthenticated;
}

// Function to fix missing employee_no in existing sessions
function fixMissingEmployeeNo() {
    // Only fix if employee_no is missing
    if (!isset($_SESSION['employee_no'])) {
        // For super admin
        if (isset($_SESSION['super_admin_logged_in']) && $_SESSION['super_admin_logged_in'] === true) {
            $_SESSION['employee_no'] = 'SUPER_ADMIN';
        }
        // For regular users, we need to fetch from database
        elseif (isset($_SESSION['user_id']) && isset($_SESSION['username'])) {
            try {
                require_once __DIR__ . '/super_admin-mis/includes/db_connection.php';
                global $conn;
                
                $query = "SELECT employee_no FROM users WHERE id = ? AND institutional_email = ?";
                $stmt = $conn->prepare($query);
                $stmt->bind_param("is", $_SESSION['user_id'], $_SESSION['username']);
                $stmt->execute();
                $result = $stmt->get_result();
                
                if ($result->num_rows > 0) {
                    $user = $result->fetch_assoc();
                    $_SESSION['employee_no'] = $user['employee_no'];
                }
            } catch (Exception $e) {
                error_log("Error fixing employee_no: " . $e->getMessage());
            }
        }
    }
}

// Function to extend session
function extendSession() {
    if (session_status() == PHP_SESSION_NONE) {
        session_start();
    }
    
    // Don't regenerate session ID to prevent session corruption
    // Only regenerate if absolutely necessary (e.g., security concerns)
    // For now, let's keep sessions stable
}

// Function to handle session timeout
function handleSessionTimeout() {
    // Super Admin should never have session timeout - they manage the system
    if (isset($_SESSION['super_admin_logged_in']) && $_SESSION['super_admin_logged_in'] === true) {
        // Log that Super Admin session timeout was prevented
        error_log("Super Admin session timeout prevented at: " . date('Y-m-d H:i:s'));
        return; // Skip session timeout for Super Admin
    }
    
    if (!isUserActive()) {
        // Update user's logout status before destroying session
        $employee_no = $_SESSION['employee_no'] ?? null;
        if ($employee_no && $employee_no !== 'SUPER_ADMIN') {
            updateUserForcedLogout($employee_no);
        }
        
        // Clear session and redirect to login
        session_destroy();
        
        // Determine redirect URL based on current page
        $currentUrl = $_SERVER['REQUEST_URI'];
        if (strpos($currentUrl, 'super_admin-mis') !== false) {
            header("Location: ../index.php");
        } else {
            header("Location: ../user_login.php");
        }
        exit();
    }
}

// Function to update user status when forcibly logged out
function updateUserForcedLogout($employee_no) {
    if (!$employee_no || $employee_no === 'SUPER_ADMIN') {
        return; // Skip for super admin or missing employee number
    }
    
    try {
        require_once __DIR__ . '/super_admin-mis/includes/db_connection.php';
        
        // Set online_status to 'offline' and update last_logout
        try {
            $updateQuery = "UPDATE users SET online_status = 'offline', last_logout = NOW() WHERE employee_no = ?";
            $updateStmt = $conn->prepare($updateQuery);
            $updateStmt->bind_param("s", $employee_no);
            $updateStmt->execute();
            $updateStmt->close();
            error_log("User forced logout status updated for: " . $employee_no);
        } catch (Exception $e) {
            // If new columns don't exist, just log the logout
            error_log("New columns not found during forced logout: " . $e->getMessage());
        }
        
        $conn->close();
    } catch (Exception $e) {
        error_log("Error updating user forced logout status: " . $e->getMessage());
    }
}

// Auto-configure session when this file is included
// Only configure if session hasn't started yet
if (session_status() == PHP_SESSION_NONE) {
    configureExtendedSession();
}
?> 