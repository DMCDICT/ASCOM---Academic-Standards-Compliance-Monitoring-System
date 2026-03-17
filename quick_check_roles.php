<?php
// quick_check_roles.php
require_once 'super_admin-mis/includes/db_connection.php';

echo "<h2>Quick Role Check</h2>";

if ($conn->connect_error) {
    echo "<p style='color: red;'>❌ Database connection failed: " . $conn->connect_error . "</p>";
    exit;
}

echo "<p style='color: green;'>✅ Database connected successfully</p>";

// Check if user_roles table has any data
echo "<h3>1. User Roles Table Status:</h3>";
$countQuery = "SELECT COUNT(*) as total FROM user_roles WHERE is_active = 1";
$countResult = $conn->query($countQuery);
$count = $countResult->fetch_assoc()['total'];

echo "<p>Total active role assignments: <strong>" . $count . "</strong></p>";

if ($count == 0) {
    echo "<p style='color: orange;'>⚠️ No role assignments found! This is why you're not seeing the role selection.</p>";
    echo "<p>You need to assign roles to users first.</p>";
} else {
    // Show all role assignments
    $rolesQuery = "
        SELECT 
            ur.id,
            ur.user_id,
            ur.role_name,
            u.first_name,
            u.last_name,
            u.institutional_email
        FROM user_roles ur
        JOIN users u ON ur.user_id = u.id
        WHERE ur.is_active = 1
        ORDER BY u.first_name, ur.role_name
    ";
    $rolesResult = $conn->query($rolesQuery);
    
    echo "<table border='1' style='border-collapse: collapse; width: 100%;'>";
    echo "<tr style='background-color: #f0f0f0;'>";
    echo "<th>User</th><th>Email</th><th>Role</th>";
    echo "</tr>";
    
    while ($row = $rolesResult->fetch_assoc()) {
        echo "<tr>";
        echo "<td>" . $row['first_name'] . " " . $row['last_name'] . "</td>";
        echo "<td>" . $row['institutional_email'] . "</td>";
        echo "<td>" . $row['role_name'] . "</td>";
        echo "</tr>";
    }
    echo "</table>";
}

// Check for users with multiple roles
echo "<h3>2. Users with Multiple Roles:</h3>";
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
    echo "<p style='color: green;'>✅ Found users with multiple roles:</p>";
    echo "<table border='1' style='border-collapse: collapse; width: 100%;'>";
    echo "<tr style='background-color: #e8f5e8;'>";
    echo "<th>User</th><th>Email</th><th>Role Count</th><th>Roles</th>";
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
    echo "<p style='color: red;'>❌ No users with multiple roles found!</p>";
    echo "<p>This is why you're not seeing the role selection screen.</p>";
}

// Show available users for role assignment
echo "<h3>3. Available Users for Role Assignment:</h3>";
$usersQuery = "
    SELECT 
        u.id,
        u.first_name,
        u.last_name,
        u.institutional_email,
        COUNT(ur.id) as current_roles
    FROM users u
    LEFT JOIN user_roles ur ON u.id = ur.user_id AND ur.is_active = 1
    WHERE u.is_active = 1
    GROUP BY u.id
    ORDER BY u.first_name
    LIMIT 10
";
$usersResult = $conn->query($usersQuery);

if ($usersResult && $usersResult->num_rows > 0) {
    echo "<table border='1' style='border-collapse: collapse; width: 100%;'>";
    echo "<tr style='background-color: #f8f9fa;'>";
    echo "<th>User</th><th>Email</th><th>Current Roles</th>";
    echo "</tr>";
    
    while ($row = $usersResult->fetch_assoc()) {
        echo "<tr>";
        echo "<td>" . $row['first_name'] . " " . $row['last_name'] . "</td>";
        echo "<td>" . $row['institutional_email'] . "</td>";
        echo "<td>" . $row['current_roles'] . "</td>";
        echo "</tr>";
    }
    echo "</table>";
} else {
    echo "<p>No users found.</p>";
}

echo "<h3>4. Next Steps:</h3>";
echo "<div style='background-color: #e8f5e8; padding: 15px; border-radius: 5px;'>";
if ($count == 0) {
    echo "<p><strong>Step 1:</strong> You need to assign roles to users first.</p>";
    echo "<p><a href='assign_test_roles.php' style='background-color: #4CAF50; color: white; padding: 10px 20px; text-decoration: none; border-radius: 5px;'>🔧 Assign Test Roles</a></p>";
} else if ($multiRoleResult->num_rows == 0) {
    echo "<p><strong>Step 1:</strong> You need to assign multiple roles to at least one user.</p>";
    echo "<p><a href='assign_test_roles.php' style='background-color: #4CAF50; color: white; padding: 10px 20px; text-decoration: none; border-radius: 5px;'>🔧 Assign Multiple Roles</a></p>";
} else {
    echo "<p><strong>Step 1:</strong> You have users with multiple roles. Test the login flow!</p>";
    echo "<p><a href='user_login.php' style='background-color: #4CAF50; color: white; padding: 10px 20px; text-decoration: none; border-radius: 5px;'>🚀 Test Login</a></p>";
}

echo "<p><strong>Step 2:</strong> <a href='test_complete_login_flow.php' style='background-color: #2196F3; color: white; padding: 10px 20px; text-decoration: none; border-radius: 5px;'>📊 View Complete Test</a></p>";
echo "</div>";

$conn->close();
?>
