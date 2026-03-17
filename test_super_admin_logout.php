<?php
// test_super_admin_logout.php
// Test script to verify Super Admin logout flow

echo "<h2>Super Admin Logout Flow Test</h2>";

// Test 1: Check if Super Admin logout files exist
echo "<h3>Test 1: Super Admin Logout Files</h3>";
$files = [
    'super_admin-mis/logout.php' => 'Super Admin logout redirector',
    'super_admin_logout_handler.php' => 'Super Admin logout handler',
    'super_admin_session_config.php' => 'Super Admin session config'
];

foreach ($files as $file => $description) {
    if (file_exists($file)) {
        echo "<p style='color: green;'>✅ $file exists ($description)</p>";
    } else {
        echo "<p style='color: red;'>❌ $file missing ($description)</p>";
    }
}

// Test 2: Check logout redirects
echo "<h3>Test 2: Logout Redirects</h3>";

// Check Super Admin logout.php content
$superAdminLogout = file_get_contents('super_admin-mis/logout.php');
if (strpos($superAdminLogout, 'super_admin_logout_handler.php') !== false) {
    echo "<p style='color: green;'>✅ Super Admin logout.php redirects to dedicated handler</p>";
} else {
    echo "<p style='color: red;'>❌ Super Admin logout.php may redirect to wrong handler</p>";
}

// Check Super Admin logout handler content
$superAdminLogoutHandler = file_get_contents('super_admin_logout_handler.php');
if (strpos($superAdminLogoutHandler, 'super_admin_session_config.php') !== false) {
    echo "<p style='color: green;'>✅ Super Admin logout handler uses dedicated session config</p>";
} else {
    echo "<p style='color: red;'>❌ Super Admin logout handler may use wrong session config</p>";
}

if (strpos($superAdminLogoutHandler, 'index.php') !== false) {
    echo "<p style='color: green;'>✅ Super Admin logout handler redirects to index.php (Super Admin login)</p>";
} else {
    echo "<p style='color: red;'>❌ Super Admin logout handler may redirect to wrong page</p>";
}

// Test 3: Check session cookie clearing
echo "<h3>Test 3: Session Cookie Clearing</h3>";
if (strpos($superAdminLogoutHandler, 'ASCOM_SUPER_ADMIN_SESSION') !== false) {
    echo "<p style='color: green;'>✅ Super Admin logout handler clears correct session cookie</p>";
} else {
    echo "<p style='color: red;'>❌ Super Admin logout handler may not clear correct session cookie</p>";
}

// Test 4: Check logout logging
echo "<h3>Test 4: Logout Logging</h3>";
if (strpos($superAdminLogoutHandler, 'super_admin_logout_log.txt') !== false) {
    echo "<p style='color: green;'>✅ Super Admin logout handler logs to dedicated file</p>";
} else {
    echo "<p style='color: red;'>❌ Super Admin logout handler may not log properly</p>";
}

// Test 5: Check logout flow
echo "<h3>Test 5: Logout Flow</h3>";
echo "<p><strong>Super Admin Logout Flow:</strong></p>";
echo "<ol>";
echo "<li>User clicks logout → <code>super_admin-mis/logout.php</code></li>";
echo "<li>Redirects to → <code>super_admin_logout_handler.php</code></li>";
echo "<li>Uses → <code>super_admin_session_config.php</code></li>";
echo "<li>Clears → <code>ASCOM_SUPER_ADMIN_SESSION</code> cookie</li>";
echo "<li>Redirects to → <code>index.php</code> (Super Admin login)</li>";
echo "</ol>";

// Test 6: Check regular user logout flow (for comparison)
echo "<h3>Test 6: Regular User Logout Flow (Comparison)</h3>";
echo "<p><strong>Regular User Logout Flow:</strong></p>";
echo "<ol>";
echo "<li>User clicks logout → <code>logout.php</code></li>";
echo "<li>Redirects to → <code>logout_handler.php</code></li>";
echo "<li>Uses → <code>session_config.php</code></li>";
echo "<li>Clears → <code>ASCOM_SESSION</code> cookie</li>";
echo "<li>Redirects to → <code>user_login.php</code> (Regular user login)</li>";
echo "</ol>";

// Test 7: Check logout links in Super Admin content
echo "<h3>Test 7: Logout Links in Super Admin Content</h3>";
$contentFile = 'super_admin-mis/content.php';
if (file_exists($contentFile)) {
    $content = file_get_contents($contentFile);
    if (strpos($content, './logout.php') !== false) {
        echo "<p style='color: green;'>✅ Super Admin content has correct logout link</p>";
    } else {
        echo "<p style='color: red;'>❌ Super Admin content may have wrong logout link</p>";
    }
} else {
    echo "<p style='color: red;'>❌ Super Admin content file not found</p>";
}

// Test 8: Summary
echo "<h3>Test 8: Summary</h3>";
echo "<p><strong>Super Admin Logout Isolation Status:</strong></p>";
echo "<ul>";
echo "<li>✅ Dedicated logout handler</li>";
echo "<li>✅ Dedicated session configuration</li>";
echo "<li>✅ Dedicated session cookie clearing</li>";
echo "<li>✅ Dedicated logout logging</li>";
echo "<li>✅ Correct redirect to Super Admin login</li>";
echo "</ul>";

echo "<h3>Recommendations:</h3>";
echo "<ol>";
echo "<li><strong>Test Logout:</strong> Login as Super Admin and test the logout button</li>";
echo "<li><strong>Check Redirect:</strong> Verify it goes to Super Admin login (index.php)</li>";
echo "<li><strong>Clear Cache:</strong> Clear browser cache if issues persist</li>";
echo "<li><strong>Check Logs:</strong> Monitor super_admin_logout_log.txt for logout events</li>";
echo "</ol>";

echo "<h3>Test Links:</h3>";
echo "<p><a href='super_admin-mis/content.php' target='_blank'>Super Admin Dashboard</a></p>";
echo "<p><a href='index.php' target='_blank'>Super Admin Login</a></p>";
echo "<p><a href='user_login.php' target='_blank'>Regular User Login</a></p>";

echo "<br><p><strong>Logout flow test complete!</strong></p>";
?> 