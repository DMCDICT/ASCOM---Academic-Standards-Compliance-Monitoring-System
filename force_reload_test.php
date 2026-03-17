<?php
// force_reload_test.php
// Simple script to test page reload and clear caching

echo "<h2>Force Reload Test</h2>";

echo "<p>This script will help test if the User Account Management page is working properly.</p>";

echo "<h3>Steps to Test:</h3>";
echo "<ol>";
echo "<li><strong>Clear browser cache</strong> (Ctrl+F5 or Ctrl+Shift+R)</li>";
echo "<li><strong>Open browser developer tools</strong> (F12)</li>";
echo "<li><strong>Go to Console tab</strong></li>";
echo "<li><strong>Click the links below</strong></li>";
echo "</ol>";

echo "<h3>Test Links:</h3>";
echo "<p><a href='super_admin-mis/content.php?page=user-account-management' target='_blank'>🔗 Open User Account Management</a></p>";
echo "<p><a href='test_api_response.php' target='_blank'>🔗 Test API Response</a></p>";
echo "<p><a href='force_refresh_users.php' target='_blank'>🔗 Force Refresh Users</a></p>";
echo "<p><a href='test_table_status.php' target='_blank'>🔗 Test Table Status</a></p>";

echo "<h3>What to Look For:</h3>";
echo "<ul>";
echo "<li>✅ <strong>Console logs</strong> showing API calls</li>";
echo "<li>✅ <strong>No JavaScript errors</strong> in the console</li>";
echo "<li>✅ <strong>Refresh button</strong> should work</li>";
echo "<li>✅ <strong>Debug button</strong> should show user data</li>";
echo "<li>✅ <strong>Online status</strong> should show correctly</li>";
echo "</ul>";

echo "<h3>If Still Not Working:</h3>";
echo "<p>1. <strong>Hard refresh</strong> the page (Ctrl+Shift+R)</p>";
echo "<p>2. <strong>Clear browser cache</strong> completely</p>";
echo "<p>3. <strong>Try incognito/private mode</strong></p>";
echo "<p>4. <strong>Check network tab</strong> in developer tools for API calls</p>";

echo "<script>";
echo "console.log('Force reload test script loaded');";
echo "console.log('Current time:', new Date().toLocaleString());";
echo "</script>";
?> 