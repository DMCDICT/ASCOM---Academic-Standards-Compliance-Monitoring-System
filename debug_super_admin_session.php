<?php
// debug_super_admin_session.php
// Debug script to test Super Admin session configuration

echo "<h2>Super Admin Session Debug</h2>";

// Test 1: Check session configuration
echo "<h3>Test 1: Session Configuration</h3>";
echo "<p><strong>Current Session Name:</strong> " . session_name() . "</p>";
echo "<p><strong>Expected Super Admin Session Name:</strong> ASCOM_SUPER_ADMIN_SESSION</p>";

// Test 2: Check if Super Admin session config is loaded
echo "<h3>Test 2: Super Admin Session Config</h3>";
if (file_exists('super_admin_session_config.php')) {
    echo "<p style='color: green;'>✅ super_admin_session_config.php exists</p>";
    
    // Include the config
    require_once 'super_admin_session_config.php';
    
    echo "<p><strong>Session Name After Config:</strong> " . session_name() . "</p>";
    
    if (session_name() === 'ASCOM_SUPER_ADMIN_SESSION') {
        echo "<p style='color: green;'>✅ Session name correctly set to ASCOM_SUPER_ADMIN_SESSION</p>";
    } else {
        echo "<p style='color: red;'>❌ Session name not set correctly</p>";
    }
} else {
    echo "<p style='color: red;'>❌ super_admin_session_config.php missing</p>";
}

// Test 3: Check session status
echo "<h3>Test 3: Session Status</h3>";
echo "<p><strong>Session Status:</strong> ";
switch (session_status()) {
    case PHP_SESSION_DISABLED:
        echo "Sessions are disabled";
        break;
    case PHP_SESSION_NONE:
        echo "Sessions are enabled but none exists";
        break;
    case PHP_SESSION_ACTIVE:
        echo "Sessions are enabled and one exists";
        break;
}
echo "</p>";

// Test 4: Start session and check variables
echo "<h3>Test 4: Session Variables</h3>";
if (session_status() == PHP_SESSION_NONE) {
    session_start();
    echo "<p style='color: blue;'>ℹ️ Session started</p>";
}

echo "<p><strong>Session Variables:</strong></p>";
if (empty($_SESSION)) {
    echo "<p style='color: orange;'>⚠️ No session variables found</p>";
} else {
    echo "<pre>" . print_r($_SESSION, true) . "</pre>";
}

// Test 5: Check authentication function
echo "<h3>Test 5: Authentication Function</h3>";
if (function_exists('isSuperAdminAuthenticated')) {
    $isAuth = isSuperAdminAuthenticated();
    echo "<p><strong>isSuperAdminAuthenticated():</strong> " . ($isAuth ? 'TRUE' : 'FALSE') . "</p>";
    
    if ($isAuth) {
        echo "<p style='color: green;'>✅ Super Admin is authenticated</p>";
    } else {
        echo "<p style='color: red;'>❌ Super Admin is not authenticated</p>";
    }
} else {
    echo "<p style='color: red;'>❌ isSuperAdminAuthenticated() function not found</p>";
}

// Test 6: Test session security function
echo "<h3>Test 6: Session Security Function</h3>";
if (function_exists('secureSuperAdminSession')) {
    echo "<p style='color: blue;'>ℹ️ Testing secureSuperAdminSession()...</p>";
    secureSuperAdminSession();
    
    echo "<p><strong>Session Variables After Security:</strong></p>";
    echo "<pre>" . print_r($_SESSION, true) . "</pre>";
    
    // Check if required variables are set
    $requiredVars = ['super_admin_logged_in', 'user_role', 'employee_no', 'username', 'user_id'];
    $allSet = true;
    
    foreach ($requiredVars as $var) {
        if (isset($_SESSION[$var])) {
            echo "<p style='color: green;'>✅ \$_SESSION['$var'] = " . $_SESSION[$var] . "</p>";
        } else {
            echo "<p style='color: red;'>❌ \$_SESSION['$var'] not set</p>";
            $allSet = false;
        }
    }
    
    if ($allSet) {
        echo "<p style='color: green;'>✅ All required session variables are set</p>";
    } else {
        echo "<p style='color: red;'>❌ Some required session variables are missing</p>";
    }
} else {
    echo "<p style='color: red;'>❌ secureSuperAdminSession() function not found</p>";
}

// Test 7: Check authentication again
echo "<h3>Test 7: Authentication After Security</h3>";
$isAuthAfter = isSuperAdminAuthenticated();
echo "<p><strong>isSuperAdminAuthenticated() After Security:</strong> " . ($isAuthAfter ? 'TRUE' : 'FALSE') . "</p>";

if ($isAuthAfter) {
    echo "<p style='color: green;'>✅ Super Admin authentication successful after security</p>";
} else {
    echo "<p style='color: red;'>❌ Super Admin authentication failed after security</p>";
}

// Test 8: Check redirect URLs
echo "<h3>Test 8: Redirect URLs</h3>";
echo "<p><strong>Super Admin Dashboard URL:</strong> <a href='super_admin-mis/content.php' target='_blank'>super_admin-mis/content.php</a></p>";
echo "<p><strong>Super Admin Successful Login URL:</strong> <a href='super_admin_successful_login.php' target='_blank'>super_admin_successful_login.php</a></p>";

// Test 9: Summary
echo "<h3>Test 9: Summary</h3>";
echo "<p><strong>Session Configuration:</strong> " . (session_name() === 'ASCOM_SUPER_ADMIN_SESSION' ? '✅ Correct' : '❌ Incorrect') . "</p>";
echo "<p><strong>Session Variables:</strong> " . (!empty($_SESSION) ? '✅ Present' : '❌ Missing') . "</p>";
echo "<p><strong>Authentication:</strong> " . ($isAuthAfter ? '✅ Working' : '❌ Failed') . "</p>";

echo "<h3>Recommendations:</h3>";
if (session_name() !== 'ASCOM_SUPER_ADMIN_SESSION') {
    echo "<p style='color: red;'>❌ Clear browser cache and cookies, then try again</p>";
}
if (!$isAuthAfter) {
    echo "<p style='color: red;'>❌ Session variables may not be set correctly</p>";
}
if (empty($_SESSION)) {
    echo "<p style='color: red;'>❌ No session data found</p>";
}

echo "<br><p><strong>Debug complete!</strong></p>";
echo "<p><a href='super_admin-mis/content.php' target='_blank'>Test Super Admin Dashboard</a></p>";
?> 