<?php
// debug_super_admin_logout.php
// Comprehensive debug script for Super Admin logout issues

require_once 'session_config.php';

// Start session
session_start();

echo "<h2>Super Admin Logout Debug</h2>";

// Check session status
echo "<h3>Session Status:</h3>";
echo "<p><strong>Session ID:</strong> " . session_id() . "</p>";
echo "<p><strong>Session Name:</strong> " . session_name() . "</p>";
echo "<p><strong>Session Status:</strong> " . session_status() . "</p>";

// Check session data
echo "<h3>Session Data:</h3>";
echo "<pre>";
print_r($_SESSION);
echo "</pre>";

// Check Super Admin authentication
echo "<h3>Super Admin Authentication Check:</h3>";
$isSuperAdminLoggedIn = false;
$authMethods = [];

if (isset($_SESSION['super_admin_logged_in']) && $_SESSION['super_admin_logged_in'] === true) {
    $isSuperAdminLoggedIn = true;
    $authMethods[] = 'super_admin_logged_in flag';
}

if (isset($_SESSION['user_role']) && $_SESSION['user_role'] === 'super_admin') {
    $isSuperAdminLoggedIn = true;
    $authMethods[] = 'user_role';
}

if (isset($_SESSION['employee_no']) && $_SESSION['employee_no'] === 'SUPER_ADMIN') {
    $isSuperAdminLoggedIn = true;
    $authMethods[] = 'employee_no';
}

if ($isSuperAdminLoggedIn) {
    echo "<p style='color: green;'>✅ Super Admin is logged in</p>";
    echo "<p><strong>Authentication Methods:</strong> " . implode(', ', $authMethods) . "</p>";
} else {
    echo "<p style='color: red;'>❌ Super Admin is NOT logged in</p>";
}

// Check session functions
echo "<h3>Session Function Tests:</h3>";

// Test isUserActive()
try {
    $isActive = isUserActive();
    echo "<p><strong>isUserActive():</strong> " . ($isActive ? 'TRUE' : 'FALSE') . "</p>";
} catch (Exception $e) {
    echo "<p style='color: red;'>❌ isUserActive() failed: " . $e->getMessage() . "</p>";
}

// Test extendSession()
try {
    extendSession();
    echo "<p style='color: green;'>✅ extendSession() completed successfully</p>";
} catch (Exception $e) {
    echo "<p style='color: red;'>❌ extendSession() failed: " . $e->getMessage() . "</p>";
}

// Check for any error logs
echo "<h3>Error Log Check:</h3>";
$errorLogPath = ini_get('error_log');
if ($errorLogPath && file_exists($errorLogPath)) {
    $recentErrors = file_get_contents($errorLogPath);
    if (strpos($recentErrors, 'session') !== false || strpos($recentErrors, 'Super Admin') !== false || strpos($recentErrors, 'logout') !== false) {
        echo "<p style='color: orange;'>⚠️ Recent errors found in error log:</p>";
        echo "<pre>" . htmlspecialchars(substr($recentErrors, -2000)) . "</pre>";
    } else {
        echo "<p style='color: green;'>✅ No recent session-related errors found</p>";
    }
} else {
    echo "<p style='color: blue;'>ℹ️ Error log not available or empty</p>";
}

// Check for logout logs
echo "<h3>Logout Log Check:</h3>";
if (file_exists('logout_log.txt')) {
    $logoutLog = file_get_contents('logout_log.txt');
    echo "<p style='color: orange;'>⚠️ Recent logout events found:</p>";
    echo "<pre>" . htmlspecialchars(substr($logoutLog, -1000)) . "</pre>";
} else {
    echo "<p style='color: green;'>✅ No logout logs found</p>";
}

// Check for Super Admin debug logs
echo "<h3>Super Admin Debug Log Check:</h3>";
if (file_exists('super_admin_debug.txt')) {
    $debugLog = file_get_contents('super_admin_debug.txt');
    echo "<p style='color: blue;'>ℹ️ Recent Super Admin debug events:</p>";
    echo "<pre>" . htmlspecialchars(substr($debugLog, -2000)) . "</pre>";
} else {
    echo "<p style='color: blue;'>ℹ️ No Super Admin debug log found</p>";
}

// Test session regeneration
echo "<h3>Session Regeneration Test:</h3>";
$oldSessionId = session_id();
echo "<p><strong>Current Session ID:</strong> " . $oldSessionId . "</p>";

// Check session configuration
echo "<h3>Session Configuration:</h3>";
echo "<p><strong>Session Lifetime:</strong> " . ini_get('session.gc_maxlifetime') . " seconds</p>";
echo "<p><strong>Cookie Lifetime:</strong> " . ini_get('session.cookie_lifetime') . " seconds</p>";
echo "<p><strong>Use Cookies:</strong> " . ini_get('session.use_cookies') . "</p>";
echo "<p><strong>Use Only Cookies:</strong> " . ini_get('session.use_only_cookies') . "</p>";
echo "<p><strong>Use Strict Mode:</strong> " . ini_get('session.use_strict_mode') . "</p>";

// Check for potential session corruption
echo "<h3>Session Integrity Check:</h3>";
$sessionCorrupted = false;
$corruptionIssues = [];

if (empty($_SESSION)) {
    $sessionCorrupted = true;
    $corruptionIssues[] = 'Session is completely empty';
}

if (isset($_SESSION['super_admin_logged_in']) && !$_SESSION['super_admin_logged_in']) {
    $sessionCorrupted = true;
    $corruptionIssues[] = 'super_admin_logged_in is false';
}

if (isset($_SESSION['user_role']) && $_SESSION['user_role'] !== 'super_admin') {
    $sessionCorrupted = true;
    $corruptionIssues[] = 'user_role is not super_admin';
}

if (isset($_SESSION['employee_no']) && $_SESSION['employee_no'] !== 'SUPER_ADMIN') {
    $sessionCorrupted = true;
    $corruptionIssues[] = 'employee_no is not SUPER_ADMIN';
}

if ($sessionCorrupted) {
    echo "<p style='color: red;'>❌ Session appears to be corrupted</p>";
    echo "<p><strong>Issues:</strong></p>";
    echo "<ul>";
    foreach ($corruptionIssues as $issue) {
        echo "<li>" . htmlspecialchars($issue) . "</li>";
    }
    echo "</ul>";
} else {
    echo "<p style='color: green;'>✅ Session appears to be intact</p>";
}

// Provide debugging actions
echo "<h3>Debug Actions:</h3>";
echo "<form method='post'>";
echo "<input type='submit' name='test_reload' value='Test Page Reload' style='padding: 10px; margin: 5px; background: #007bff; color: white; border: none; border-radius: 5px; cursor: pointer;'>";
echo "<input type='submit' name='clear_logs' value='Clear All Logs' style='padding: 10px; margin: 5px; background: #dc3545; color: white; border: none; border-radius: 5px; cursor: pointer;'>";
echo "<input type='submit' name='force_super_admin' value='Force Super Admin Session' style='padding: 10px; margin: 5px; background: #28a745; color: white; border: none; border-radius: 5px; cursor: pointer;'>";
echo "</form>";

if ($_POST['test_reload']) {
    echo "<p style='color: blue;'>🔄 Testing page reload...</p>";
    echo "<script>setTimeout(() => { window.location.reload(); }, 2000);</script>";
}

if ($_POST['clear_logs']) {
    if (file_exists('super_admin_debug.txt')) {
        unlink('super_admin_debug.txt');
    }
    if (file_exists('logout_log.txt')) {
        unlink('logout_log.txt');
    }
    echo "<p style='color: green;'>✅ All logs cleared</p>";
}

if ($_POST['force_super_admin']) {
    // Force set Super Admin session variables
    $_SESSION['super_admin_logged_in'] = true;
    $_SESSION['user_role'] = 'super_admin';
    $_SESSION['employee_no'] = 'SUPER_ADMIN';
    $_SESSION['username'] = 'super_admin@ascom.edu.ph';
    echo "<p style='color: green;'>✅ Super Admin session forced</p>";
    echo "<script>setTimeout(() => { window.location.reload(); }, 1000);</script>";
}

echo "<br><p><strong>Debug complete!</strong></p>";
echo "<p><a href='super_admin-mis/content.php'>Go to Super Admin Dashboard</a></p>";
echo "<p><a href='index.php'>Go to Super Admin Login</a></p>";
?> 