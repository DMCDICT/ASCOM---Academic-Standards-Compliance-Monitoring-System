<?php
// fix_session.php
// Script to fix missing employee_no in current session

require_once 'session_config.php';

// Start session
session_start();

echo "<h2>Fixing Current Session</h2>";

// Check current session
echo "<h3>Before Fix:</h3>";
echo "<pre>";
print_r($_SESSION);
echo "</pre>";

// Force fix the missing employee_no
fixMissingEmployeeNo();

echo "<h3>After Fix:</h3>";
echo "<pre>";
print_r($_SESSION);
echo "</pre>";

if (isset($_SESSION['employee_no'])) {
    echo "<p style='color: green;'>✅ Employee number fixed: " . $_SESSION['employee_no'] . "</p>";
} else {
    echo "<p style='color: red;'>❌ Still missing employee number</p>";
}

echo "<br><p><a href='test_activity.php'>Test Activity Again</a></p>";
echo "<p><a href='super_admin-mis/content.php?page=user-account-management'>Go to User Account Management</a></p>";
?> 