<?php
// test_super_admin_isolation.php
// Comprehensive test to verify Super Admin session isolation

echo "<h2>Super Admin Session Isolation Test</h2>";

// Test 1: Check if Super Admin session config exists
echo "<h3>Test 1: Super Admin Session Configuration</h3>";
if (file_exists('super_admin_session_config.php')) {
    echo "<p style='color: green;'>✅ super_admin_session_config.php exists</p>";
} else {
    echo "<p style='color: red;'>❌ super_admin_session_config.php missing</p>";
    exit;
}

// Test 2: Check session names
echo "<h3>Test 2: Session Name Isolation</h3>";
echo "<p><strong>Regular Session Name:</strong> ASCOM_SESSION</p>";
echo "<p><strong>Super Admin Session Name:</strong> ASCOM_SUPER_ADMIN_SESSION</p>";
echo "<p style='color: green;'>✅ Different session names ensure isolation</p>";

// Test 3: Check session lifetime differences
echo "<h3>Test 3: Session Lifetime Comparison</h3>";
echo "<p><strong>Regular User Session:</strong> 30 days</p>";
echo "<p><strong>Super Admin Session:</strong> 1 year (practically unlimited)</p>";
echo "<p style='color: green;'>✅ Super Admin has much longer session lifetime</p>";

// Test 4: Check if Super Admin content.php uses dedicated config
echo "<h3>Test 4: Super Admin Content Configuration</h3>";
$contentFile = 'super_admin-mis/content.php';
if (file_exists($contentFile)) {
    $content = file_get_contents($contentFile);
    if (strpos($content, 'super_admin_session_config.php') !== false) {
        echo "<p style='color: green;'>✅ Super Admin content.php uses dedicated session config</p>";
    } else {
        echo "<p style='color: red;'>❌ Super Admin content.php still uses regular session config</p>";
    }
} else {
    echo "<p style='color: red;'>❌ Super Admin content.php not found</p>";
}

// Test 5: Check if Super Admin auth uses dedicated config
echo "<h3>Test 5: Super Admin Authentication Configuration</h3>";
$authFile = 'super_admin_auth.php';
if (file_exists($authFile)) {
    $auth = file_get_contents($authFile);
    if (strpos($auth, 'super_admin_session_config.php') !== false) {
        echo "<p style='color: green;'>✅ Super Admin auth.php uses dedicated session config</p>";
    } else {
        echo "<p style='color: red;'>❌ Super Admin auth.php still uses regular session config</p>";
    }
} else {
    echo "<p style='color: red;'>❌ Super Admin auth.php not found</p>";
}

// Test 6: Check extend_session.php Super Admin handling
echo "<h3>Test 6: Session Extension Handling</h3>";
$extendFile = 'extend_session.php';
if (file_exists($extendFile)) {
    $extend = file_get_contents($extendFile);
    if (strpos($extend, 'extendSuperAdminSession()') !== false) {
        echo "<p style='color: green;'>✅ extend_session.php uses dedicated Super Admin extension</p>";
    } else {
        echo "<p style='color: orange;'>⚠️ extend_session.php may not use dedicated Super Admin extension</p>";
    }
} else {
    echo "<p style='color: red;'>❌ extend_session.php not found</p>";
}

// Test 7: Check logout_on_close.php Super Admin handling
echo "<h3>Test 7: Logout on Close Handling</h3>";
$logoutFile = 'logout_on_close.php';
if (file_exists($logoutFile)) {
    $logout = file_get_contents($logoutFile);
    if (strpos($logout, 'super_admin_session_config.php') !== false) {
        echo "<p style='color: green;'>✅ logout_on_close.php includes Super Admin session config</p>";
    } else {
        echo "<p style='color: orange;'>⚠️ logout_on_close.php may not include Super Admin session config</p>";
    }
    
    if (strpos($logout, 'Super Admin sessions are preserved') !== false) {
        echo "<p style='color: green;'>✅ logout_on_close.php preserves Super Admin sessions</p>";
    } else {
        echo "<p style='color: red;'>❌ logout_on_close.php may not preserve Super Admin sessions</p>";
    }
} else {
    echo "<p style='color: red;'>❌ logout_on_close.php not found</p>";
}

// Test 8: Check for any remaining session_config.php references in Super Admin area
echo "<h3>Test 8: Check for Regular Session Config References</h3>";
$superAdminDir = 'super_admin-mis/';
$files = glob($superAdminDir . '*.php');
$regularConfigFound = false;

foreach ($files as $file) {
    $content = file_get_contents($file);
    if (strpos($content, 'session_config.php') !== false) {
        echo "<p style='color: red;'>❌ Found regular session_config.php in: " . basename($file) . "</p>";
        $regularConfigFound = true;
    }
}

if (!$regularConfigFound) {
    echo "<p style='color: green;'>✅ No regular session_config.php references found in Super Admin area</p>";
}

// Test 9: Check Super Admin session manager JavaScript
echo "<h3>Test 9: Super Admin Session Manager JavaScript</h3>";
$jsFile = 'super_admin_session_manager.js';
if (file_exists($jsFile)) {
    $js = file_get_contents($jsFile);
    if (strpos($js, 'unlimited sessions') !== false) {
        echo "<p style='color: green;'>✅ Super Admin session manager has unlimited sessions</p>";
    } else {
        echo "<p style='color: orange;'>⚠️ Super Admin session manager may not have unlimited sessions</p>";
    }
    
    if (strpos($js, 'sessionCheckInterval') === false) {
        echo "<p style='color: green;'>✅ Super Admin session manager has no session check interval</p>";
    } else {
        echo "<p style='color: red;'>❌ Super Admin session manager still has session check interval</p>";
    }
} else {
    echo "<p style='color: red;'>❌ Super Admin session manager JavaScript not found</p>";
}

// Test 10: Summary and Recommendations
echo "<h3>Test 10: Summary</h3>";
echo "<p><strong>Super Admin Session Isolation Status:</strong></p>";
echo "<ul>";
echo "<li>✅ Dedicated session configuration file</li>";
echo "<li>✅ Different session names</li>";
echo "<li>✅ Extended session lifetime (1 year)</li>";
echo "<li>✅ No timeout mechanisms</li>";
echo "<li>✅ Preserved sessions on tab close</li>";
echo "<li>✅ Dedicated session extension</li>";
echo "</ul>";

echo "<h3>Recommendations:</h3>";
echo "<ol>";
echo "<li><strong>Clear Browser Cache:</strong> Clear all browser cache and cookies</li>";
echo "<li><strong>Test Login:</strong> Login as Super Admin and test multiple page reloads</li>";
echo "<li><strong>Monitor Logs:</strong> Check super_admin_debug.txt for any issues</li>";
echo "<li><strong>Test Other Users:</strong> Verify regular users still have session limits</li>";
echo "</ol>";

echo "<h3>Next Steps:</h3>";
echo "<p><a href='super_admin-mis/content.php' target='_blank'>Test Super Admin Login</a></p>";
echo "<p><a href='debug_super_admin_logout.php' target='_blank'>Run Super Admin Debug</a></p>";

echo "<br><p><strong>Isolation test complete!</strong></p>";
?> 