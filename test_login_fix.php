<?php
// test_login_fix.php
require_once 'super_admin-mis/includes/db_connection.php';

echo "<h2>Login Fix Test</h2>";

if ($conn->connect_error) {
    echo "<p style='color: red;'>❌ Database connection failed: " . $conn->connect_error . "</p>";
    exit;
}

echo "<p style='color: green;'>✅ Database connected successfully</p>";

// Test the exact query from user_auth.php
$testEmail = "philipcrisencarnacion@sccpag.edu.ph";
$testPassword = "132065TCHCCS";

echo "<h3>Testing Database Query:</h3>";
echo "<p><strong>Test User:</strong> " . $testEmail . "</p>";

// Test the exact query from user_auth.php
$stmt = $conn->prepare("SELECT id, employee_no, institutional_email, password, role_id, is_active, last_activity, first_name, last_name, title FROM users WHERE institutional_email = ? AND is_active = 1");
$stmt->bind_param("s", $testEmail);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 1) {
    $user = $result->fetch_assoc();
    echo "<p style='color: green;'>✅ User found: " . $user['first_name'] . " " . $user['last_name'] . "</p>";
    echo "<p>User ID: " . $user['id'] . "</p>";
    echo "<p>Password in DB: " . $user['password'] . "</p>";
    echo "<p>Test Password: " . $testPassword . "</p>";
    echo "<p>Password Match: " . ($testPassword === $user['password'] ? 'YES' : 'NO') . "</p>";
    
    // Test user roles query
    echo "<h3>Testing User Roles Query:</h3>";
    $roles_stmt = $conn->prepare("
        SELECT ur.role_name, ur.assigned_at
        FROM user_roles ur
        WHERE ur.user_id = ? AND ur.is_active = 1
        ORDER BY ur.assigned_at DESC
    ");
    $roles_stmt->bind_param("i", $user['id']);
    $roles_stmt->execute();
    $roles_result = $roles_stmt->get_result();
    
    if ($roles_result->num_rows > 0) {
        echo "<p style='color: green;'>✅ Found " . $roles_result->num_rows . " role(s):</p>";
        echo "<ul>";
        while ($role = $roles_result->fetch_assoc()) {
            echo "<li>" . $role['role_name'] . " (Assigned: " . $role['assigned_at'] . ")</li>";
        }
        echo "</ul>";
        
        if ($roles_result->num_rows > 1) {
            echo "<p style='color: blue;'>🔵 User has multiple roles - should see role selection</p>";
        } else {
            echo "<p style='color: orange;'>🟠 User has single role - should go directly to dashboard</p>";
        }
    } else {
        echo "<p style='color: red;'>❌ No roles found for user</p>";
    }
    
} else {
    echo "<p style='color: red;'>❌ User not found or not active</p>";
}

echo "<h3>Test Login:</h3>";
echo "<p><a href='user_login.php' style='background-color: #4CAF50; color: white; padding: 15px 30px; text-decoration: none; border-radius: 8px; margin: 10px; display: inline-block;'>🔐 Try Login Now</a></p>";

echo "<h3>Expected Behavior:</h3>";
echo "<div style='background-color: #e8f5e8; padding: 15px; border-radius: 5px;'>";
echo "<p><strong>If login works:</strong></p>";
echo "<ul>";
echo "<li>Form submits to user_auth.php</li>";
echo "<li>User is authenticated</li>";
echo "<li>Session is created</li>";
echo "<li>Redirects to role_selection.php (if multiple roles) or successful_login.php (if single role)</li>";
echo "</ul>";
echo "</div>";

$conn->close();
?>
