<?php
// test_refresh_debug.php
// Debug script to test automatic refresh

echo "<h2>Refresh Debug Test</h2>";

echo "<p>This script will help debug why the automatic refresh might not be working.</p>";

echo "<h3>Step-by-Step Debug:</h3>";
echo "<ol>";
echo "<li><strong>Open User Account Management</strong> in a new tab</li>";
echo "<li><strong>Open browser developer tools</strong> (F12)</li>";
echo "<li><strong>Go to Console tab</strong></li>";
echo "<li><strong>Look for these messages:</strong></li>";
echo "<ul>";
echo "<li>✅ <code>🔄 Loading initial user data from API...</code></li>";
echo "<li>✅ <code>✅ Initial data loaded successfully</code></li>";
echo "<li>✅ <code>🔄 Refreshing user list for real-time status updates...</code> (every 30 seconds)</li>";
echo "<li>✅ <code>✅ User list refreshed successfully</code></li>";
echo "</ul>";
echo "<li><strong>If you don't see these messages</strong>, there's a JavaScript error</li>";
echo "<li><strong>If you see error messages</strong>, check the Network tab for failed API calls</li>";
echo "</ol>";

echo "<h3>Common Issues:</h3>";
echo "<ul>";
echo "<li><strong>No console messages:</strong> JavaScript not loading or has errors</li>";
echo "<li><strong>API errors:</strong> Check Network tab for failed requests</li>";
echo "<li><strong>No automatic refresh:</strong> Variables not in global scope</li>";
echo "<li><strong>Table not updating:</strong> renderTable function not accessible</li>";
echo "</ul>";

echo "<h3>Test Links:</h3>";
echo "<p><a href='super_admin-mis/content.php?page=user-account-management' target='_blank'>🔗 Open User Account Management</a></p>";
echo "<p><a href='test_api_response.php' target='_blank'>🔗 Test API Response</a></p>";
echo "<p><a href='force_refresh_users.php' target='_blank'>🔗 Force Refresh Users</a></p>";

echo "<h3>Manual Test:</h3>";
echo "<p>1. Open User Account Management</p>";
echo "<p>2. Wait 30 seconds</p>";
echo "<p>3. Check console for refresh messages</p>";
echo "<p>4. If no messages, there's a JavaScript issue</p>";

echo "<script>";
echo "console.log('Refresh debug test loaded');";
echo "console.log('Current time:', new Date().toLocaleString());";
echo "</script>";
?> 