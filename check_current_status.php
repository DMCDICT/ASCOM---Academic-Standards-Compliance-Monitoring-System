<?php
// check_current_status.php
require_once 'super_admin-mis/includes/db_connection.php';

echo "<h2>Current System Status Check</h2>";

if ($conn->connect_error) {
    echo "<p style='color: red;'>❌ Database connection failed: " . $conn->connect_error . "</p>";
    exit;
}

echo "<p style='color: green;'>✅ Database connected successfully</p>";

// Check if user_roles table exists and has data
echo "<h3>1. User Roles Table Status:</h3>";
$tableCheck = $conn->query("SHOW TABLES LIKE 'user_roles'");
if ($tableCheck->num_rows > 0) {
    echo "<p style='color: green;'>✅ user_roles table exists</p>";
    
    $countQuery = "SELECT COUNT(*) as total FROM user_roles WHERE is_active = 1";
    $countResult = $conn->query($countQuery);
    $count = $countResult->fetch_assoc()['total'];
    echo "<p>Active role assignments: <strong>" . $count . "</strong></p>";
    
    if ($count == 0) {
        echo "<p style='color: orange;'>⚠️ No active role assignments found</p>";
    }
} else {
    echo "<p style='color: red;'>❌ user_roles table does not exist</p>";
}

// Check users table
echo "<h3>2. Users Table Status:</h3>";
$usersCheck = $conn->query("SELECT COUNT(*) as total FROM users WHERE is_active = 1");
$usersCount = $usersCheck->fetch_assoc()['total'];
echo "<p>Active users: <strong>" . $usersCount . "</strong></p>";

// Show sample users with their roles
echo "<h3>3. Sample Users and Their Roles:</h3>";
$usersQuery = "
    SELECT 
        u.id,
        u.first_name,
        u.last_name,
        u.institutional_email,
        u.password,
        u.role_id,
        COUNT(ur.id) as user_role_count,
        GROUP_CONCAT(ur.role_name SEPARATOR ', ') as user_roles
    FROM users u
    LEFT JOIN user_roles ur ON u.id = ur.user_id AND ur.is_active = 1
    WHERE u.is_active = 1
    GROUP BY u.id
    ORDER BY u.first_name
    LIMIT 5
";
$usersResult = $conn->query($usersQuery);

if ($usersResult && $usersResult->num_rows > 0) {
    echo "<table border='1' style='border-collapse: collapse; width: 100%;'>";
    echo "<tr style='background-color: #f0f0f0;'>";
    echo "<th>ID</th><th>Name</th><th>Email</th><th>Password</th><th>Legacy Role ID</th><th>User Role Count</th><th>User Roles</th>";
    echo "</tr>";
    
    while ($row = $usersResult->fetch_assoc()) {
        echo "<tr>";
        echo "<td>" . $row['id'] . "</td>";
        echo "<td>" . $row['first_name'] . " " . $row['last_name'] . "</td>";
        echo "<td>" . $row['institutional_email'] . "</td>";
        echo "<td>" . $row['password'] . "</td>";
        echo "<td>" . $row['role_id'] . "</td>";
        echo "<td>" . $row['user_role_count'] . "</td>";
        echo "<td>" . ($row['user_roles'] ?: 'None') . "</td>";
        echo "</tr>";
    }
    echo "</table>";
} else {
    echo "<p>No users found.</p>";
}

// Check for users with multiple roles
echo "<h3>4. Users with Multiple Roles:</h3>";
$multiRoleQuery = "
    SELECT 
        u.id,
        u.first_name,
        u.last_name,
        u.institutional_email,
        COUNT(ur.id) as role_count,
        GROUP_CONCAT(ur.role_name SEPARATOR ', ') as roles
    FROM users u
    JOIN user_roles ur ON u.id = ur.user_id
    WHERE ur.is_active = 1
    GROUP BY u.id
    HAVING COUNT(ur.id) > 1
    ORDER BY u.first_name
";
$multiRoleResult = $conn->query($multiRoleQuery);

if ($multiRoleResult && $multiRoleResult->num_rows > 0) {
    echo "<p style='color: green;'>✅ Found " . $multiRoleResult->num_rows . " user(s) with multiple roles:</p>";
    echo "<table border='1' style='border-collapse: collapse; width: 100%;'>";
    echo "<tr style='background-color: #e8f5e8;'>";
    echo "<th>Name</th><th>Email</th><th>Role Count</th><th>Roles</th>";
    echo "</tr>";
    
    while ($row = $multiRoleResult->fetch_assoc()) {
        echo "<tr>";
        echo "<td>" . $row['first_name'] . " " . $row['last_name'] . "</td>";
        echo "<td>" . $row['institutional_email'] . "</td>";
        echo "<td>" . $row['role_count'] . "</td>";
        echo "<td>" . $row['roles'] . "</td>";
        echo "</tr>";
    }
    echo "</table>";
} else {
    echo "<p style='color: orange;'>⚠️ No users with multiple roles found</p>";
}

// Check roles table
echo "<h3>5. Available Roles:</h3>";
$rolesQuery = "SELECT id, role FROM roles ORDER BY id";
$rolesResult = $conn->query($rolesQuery);

if ($rolesResult && $rolesResult->num_rows > 0) {
    echo "<table border='1' style='border-collapse: collapse; width: 100%;'>";
    echo "<tr style='background-color: #f8f9fa;'>";
    echo "<th>ID</th><th>Role Name</th>";
    echo "</tr>";
    
    while ($row = $rolesResult->fetch_assoc()) {
        echo "<tr>";
        echo "<td>" . $row['id'] . "</td>";
        echo "<td>" . $row['role'] . "</td>";
        echo "</tr>";
    }
    echo "</table>";
} else {
    echo "<p>No roles found.</p>";
}

echo "<h3>6. Quick Actions:</h3>";
echo "<div style='text-align: center; margin: 20px 0;'>";
echo "<a href='assign_test_roles.php' style='background-color: #4CAF50; color: white; padding: 15px 30px; text-decoration: none; border-radius: 8px; margin: 10px; display: inline-block;'>🔧 Assign Test Roles</a>";
echo "<a href='user_login.php' style='background-color: #2196F3; color: white; padding: 15px 30px; text-decoration: none; border-radius: 8px; margin: 10px; display: inline-block;'>🔐 Test Login</a>";
echo "<a href='debug_login_issue.php' style='background-color: #FF9800; color: white; padding: 15px 30px; text-decoration: none; border-radius: 8px; margin: 10px; display: inline-block;'>🔍 Debug Login</a>";
echo "</div>";

$conn->close();
?>
