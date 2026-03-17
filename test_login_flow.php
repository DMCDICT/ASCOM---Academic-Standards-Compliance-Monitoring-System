<?php
// test_login_flow.php
session_start();

echo "<h2>Login Flow Debug</h2>";

echo "<h3>1. Session Data:</h3>";
echo "<pre>";
print_r($_SESSION);
echo "</pre>";

echo "<h3>2. Authentication Status:</h3>";
if (isset($_SESSION['is_authenticated']) && $_SESSION['is_authenticated']) {
    echo "<p style='color: green;'>✅ User is authenticated</p>";
} else {
    echo "<p style='color: red;'>❌ User is not authenticated</p>";
}

echo "<h3>3. User Roles:</h3>";
if (isset($_SESSION['user_roles']) && is_array($_SESSION['user_roles'])) {
    echo "<p style='color: green;'>✅ User roles found:</p>";
    echo "<ul>";
    foreach ($_SESSION['user_roles'] as $role) {
        echo "<li>Type: " . $role['type'] . "</li>";
        echo "<li>Department: " . ($role['department_name'] ?: 'None') . "</li>";
        echo "<li>Assigned: " . ($role['assigned_at'] ?: 'Unknown') . "</li>";
        echo "<br>";
    }
    echo "</ul>";
} else {
    echo "<p style='color: red;'>❌ No user roles found</p>";
}

echo "<h3>4. Selected Role:</h3>";
if (isset($_SESSION['selected_role'])) {
    echo "<p style='color: green;'>✅ Selected role found:</p>";
    echo "<pre>";
    print_r($_SESSION['selected_role']);
    echo "</pre>";
} else {
    echo "<p style='color: red;'>❌ No selected role found</p>";
}

echo "<h3>5. Legacy Role:</h3>";
if (isset($_SESSION['user_role'])) {
    echo "<p style='color: orange;'>⚠️ Legacy role found: " . $_SESSION['user_role'] . "</p>";
} else {
    echo "<p style='color: blue;'>ℹ️ No legacy role found</p>";
}

echo "<h3>6. Role Dashboard Mapping Test:</h3>";
function getRoleDashboard($roleType) {
    switch ($roleType) {
        case 'teacher':
            return 'teacher/dashboard.php';
        case 'dean':
            return 'dean/dashboard.php';
        case 'librarian':
            return 'librarian/dashboard.php';
        case 'quality_assurance':
            return 'qa/dashboard.php';
        default:
            return 'user_login.php';
    }
}

if (isset($_SESSION['selected_role']['type'])) {
    $dashboardUrl = getRoleDashboard($_SESSION['selected_role']['type']);
    echo "<p><strong>Role Type:</strong> " . $_SESSION['selected_role']['type'] . "</p>";
    echo "<p><strong>Dashboard URL:</strong> " . $dashboardUrl . "</p>";
} else {
    echo "<p style='color: red;'>❌ Cannot determine dashboard URL - no role type found</p>";
}

echo "<h3>7. Next Steps:</h3>";
echo "<div style='background-color: #e8f5e8; padding: 15px; border-radius: 5px;'>";
echo "<p><strong>To test the login flow:</strong></p>";
echo "<ol>";
echo "<li>Login with a user account</li>";
echo "<li>Check if role selection appears (for multi-role users)</li>";
echo "<li>Verify redirect to correct dashboard</li>";
echo "</ol>";
echo "<p><a href='user_login.php' style='background-color: #4CAF50; color: white; padding: 10px 20px; text-decoration: none; border-radius: 5px;'>🚀 Test Login</a></p>";
echo "</div>";
?>
