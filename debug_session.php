<?php
// debug_session.php
// Debug script to check session status

require_once 'session_config.php';

// Start session
session_start();

echo "<h2>Session Debug Information</h2>";

echo "<h3>Session Status:</h3>";
echo "<p>Session ID: " . session_id() . "</p>";
echo "<p>Session Name: " . session_name() . "</p>";
echo "<p>Session Status: " . session_status() . "</p>";

echo "<h3>Session Variables:</h3>";
echo "<pre>";
print_r($_SESSION);
echo "</pre>";

echo "<h3>Session Configuration:</h3>";
echo "<p>Session Lifetime: " . ini_get('session.gc_maxlifetime') . " seconds</p>";
echo "<p>Cookie Lifetime: " . ini_get('session.cookie_lifetime') . " seconds</p>";
echo "<p>Use Cookies: " . ini_get('session.use_cookies') . "</p>";
echo "<p>Use Only Cookies: " . ini_get('session.use_only_cookies') . "</p>";

echo "<h3>Authentication Check:</h3>";
$isAuthenticated = false;

if (isset($_SESSION['super_admin_logged_in']) && $_SESSION['super_admin_logged_in'] === true) {
    echo "<p style='color: green;'>✅ Super Admin logged in</p>";
    $isAuthenticated = true;
} elseif (isset($_SESSION['dean_logged_in']) && $_SESSION['dean_logged_in'] === true) {
    echo "<p style='color: green;'>✅ Department Dean logged in</p>";
    $isAuthenticated = true;
} elseif (isset($_SESSION['teacher_logged_in']) && $_SESSION['teacher_logged_in'] === true) {
    echo "<p style='color: green;'>✅ Teacher logged in</p>";
    $isAuthenticated = true;
} elseif (isset($_SESSION['librarian_logged_in']) && $_SESSION['librarian_logged_in'] === true) {
    echo "<p style='color: green;'>✅ Librarian logged in</p>";
    $isAuthenticated = true;
} elseif (isset($_SESSION['admin_qa_logged_in']) && $_SESSION['admin_qa_logged_in'] === true) {
    echo "<p style='color: green;'>✅ Admin QA logged in</p>";
    $isAuthenticated = true;
} else {
    echo "<p style='color: red;'>❌ No user authenticated</p>";
}

echo "<h3>User Role:</h3>";
echo "<p>User Role: " . ($_SESSION['user_role'] ?? 'Not set') . "</p>";

echo "<h3>Actions:</h3>";
if ($isAuthenticated) {
    echo "<p><a href='super_admin-mis/content.php'>Go to Super Admin Dashboard</a></p>";
    echo "<p><a href='logout.php'>Logout</a></p>";
} else {
    echo "<p><a href='index.php'>Go to Login</a></p>";
}

echo "<h3>Session Files:</h3>";
$sessionPath = session_save_path();
echo "<p>Session Save Path: " . $sessionPath . "</p>";
if (is_dir($sessionPath)) {
    $files = scandir($sessionPath);
    echo "<p>Session Files:</p><ul>";
    foreach ($files as $file) {
        if ($file !== '.' && $file !== '..') {
            echo "<li>" . $file . "</li>";
        }
    }
    echo "</ul>";
}
?> 