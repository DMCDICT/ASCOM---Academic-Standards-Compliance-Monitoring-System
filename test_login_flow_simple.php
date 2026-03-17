<?php
// test_login_flow_simple.php
require_once 'super_admin-mis/includes/db_connection.php';

echo "<h2>Simple Login Flow Test</h2>";

if ($conn->connect_error) {
    echo "<p style='color: red;'>❌ Database connection failed: " . $conn->connect_error . "</p>";
    exit;
}

echo "<p style='color: green;'>✅ Database connected successfully</p>";

// Check current user roles
echo "<h3>Current User Roles:</h3>";
$rolesQuery = "
    SELECT 
        u.first_name,
        u.last_name,
        u.institutional_email,
        u.password,
        COUNT(ur.id) as role_count,
        GROUP_CONCAT(ur.role_name SEPARATOR ', ') as roles
    FROM users u
    LEFT JOIN user_roles ur ON u.id = ur.user_id AND ur.is_active = 1
    WHERE u.is_active = 1
    GROUP BY u.id
    ORDER BY role_count DESC, u.first_name
    LIMIT 10
";
$rolesResult = $conn->query($rolesQuery);

if ($rolesResult && $rolesResult->num_rows > 0) {
    echo "<table border='1' style='border-collapse: collapse; width: 100%;'>";
    echo "<tr style='background-color: #f0f0f0;'>";
    echo "<th>Name</th><th>Email</th><th>Password</th><th>Role Count</th><th>Roles</th><th>Expected Flow</th>";
    echo "</tr>";
    
    while ($row = $rolesResult->fetch_assoc()) {
        $expectedFlow = $row['role_count'] > 1 ? 'Login → Role Selection → Welcome → Dashboard' : 'Login → Welcome → Dashboard';
        $flowColor = $row['role_count'] > 1 ? 'green' : 'blue';
        
        echo "<tr>";
        echo "<td>" . $row['first_name'] . " " . $row['last_name'] . "</td>";
        echo "<td>" . $row['institutional_email'] . "</td>";
        echo "<td>" . $row['password'] . "</td>";
        echo "<td>" . $row['role_count'] . "</td>";
        echo "<td>" . $row['roles'] . "</td>";
        echo "<td style='color: " . $flowColor . ";'>" . $expectedFlow . "</td>";
        echo "</tr>";
    }
    echo "</table>";
} else {
    echo "<p>No users found.</p>";
}

// Check if any users have multiple roles
$multiRoleQuery = "
    SELECT COUNT(*) as multi_role_count
    FROM (
        SELECT u.id
        FROM users u
        JOIN user_roles ur ON u.id = ur.user_id
        WHERE ur.is_active = 1
        GROUP BY u.id
        HAVING COUNT(ur.id) > 1
    ) as multi_users
";
$multiRoleResult = $conn->query($multiRoleQuery);
$multiRoleCount = $multiRoleResult->fetch_assoc()['multi_role_count'];

echo "<h3>Summary:</h3>";
echo "<div style='background-color: #e8f5e8; padding: 15px; border-radius: 5px;'>";
if ($multiRoleCount > 0) {
    echo "<p style='color: green;'>✅ Found " . $multiRoleCount . " user(s) with multiple roles</p>";
    echo "<p>These users will see the role selection screen when they login.</p>";
} else {
    echo "<p style='color: orange;'>⚠️ No users have multiple roles assigned</p>";
    echo "<p>This is why you're not seeing the role selection screen.</p>";
}

echo "<p><strong>To see the role selection screen:</strong></p>";
echo "<ol>";
echo "<li>Go to <a href='assign_test_roles.php' target='_blank'>assign_test_roles.php</a></li>";
echo "<li>Select a user and assign multiple roles (e.g., teacher AND dean)</li>";
echo "<li>Login with that user's credentials</li>";
echo "<li>You should now see the role selection screen!</li>";
echo "</ol>";
echo "</div>";

echo "<h3>Quick Test:</h3>";
echo "<div style='text-align: center; margin: 20px 0;'>";
echo "<a href='assign_test_roles.php' style='background-color: #4CAF50; color: white; padding: 15px 30px; text-decoration: none; border-radius: 8px; margin: 10px; display: inline-block;'>🔧 Assign Multiple Roles</a>";
echo "<a href='user_login.php' style='background-color: #2196F3; color: white; padding: 15px 30px; text-decoration: none; border-radius: 8px; margin: 10px; display: inline-block;'>🔐 Test Login</a>";
echo "<a href='debug_login_issue.php' style='background-color: #FF9800; color: white; padding: 15px 30px; text-decoration: none; border-radius: 8px; margin: 10px; display: inline-block;'>🔍 Debug Login</a>";
echo "</div>";

$conn->close();
?>
