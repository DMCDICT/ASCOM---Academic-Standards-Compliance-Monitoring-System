<?php
// debug_role_types.php
require_once 'super_admin-mis/includes/db_connection.php';

echo "<h2>Role Types Debug</h2>";

if ($conn->connect_error) {
    echo "<p style='color: red;'>❌ Database connection failed: " . $conn->connect_error . "</p>";
    exit;
}

echo "<p style='color: green;'>✅ Database connected successfully</p>";

// Check roles table
echo "<h3>1. Roles Table:</h3>";
$rolesQuery = "SELECT * FROM roles ORDER BY id";
$rolesResult = $conn->query($rolesQuery);

if ($rolesResult && $rolesResult->num_rows > 0) {
    echo "<table border='1' style='border-collapse: collapse; margin: 10px 0;'>";
    echo "<tr><th>ID</th><th>Role Name</th></tr>";
    while ($row = $rolesResult->fetch_assoc()) {
        echo "<tr>";
        echo "<td>" . $row['id'] . "</td>";
        echo "<td>" . $row['role'] . "</td>";
        echo "</tr>";
    }
    echo "</table>";
} else {
    echo "<p>No roles found in roles table.</p>";
}

// Check user_roles table
echo "<h3>2. User Roles Table:</h3>";
$userRolesQuery = "SELECT DISTINCT role_name FROM user_roles ORDER BY role_name";
$userRolesResult = $conn->query($userRolesQuery);

if ($userRolesResult && $userRolesResult->num_rows > 0) {
    echo "<table border='1' style='border-collapse: collapse; margin: 10px 0;'>";
    echo "<tr><th>Role Name</th></tr>";
    while ($row = $userRolesResult->fetch_assoc()) {
        echo "<tr>";
        echo "<td>" . $row['role_name'] . "</td>";
        echo "</tr>";
    }
    echo "</table>";
} else {
    echo "<p>No user roles found in user_roles table.</p>";
}

// Check users table with their roles
echo "<h3>3. Users with Their Roles:</h3>";
$usersQuery = "
    SELECT 
        u.id,
        u.first_name,
        u.last_name,
        u.institutional_email,
        u.role_id,
        r.role as role_name
    FROM users u
    LEFT JOIN roles r ON u.role_id = r.id
    WHERE u.is_active = 1
    ORDER BY u.first_name
";
$usersResult = $conn->query($usersQuery);

if ($usersResult && $usersResult->num_rows > 0) {
    echo "<table border='1' style='border-collapse: collapse; margin: 10px 0;'>";
    echo "<tr><th>ID</th><th>Name</th><th>Email</th><th>Role ID</th><th>Role Name</th></tr>";
    while ($row = $usersResult->fetch_assoc()) {
        echo "<tr>";
        echo "<td>" . $row['id'] . "</td>";
        echo "<td>" . $row['first_name'] . " " . $row['last_name'] . "</td>";
        echo "<td>" . $row['institutional_email'] . "</td>";
        echo "<td>" . $row['role_id'] . "</td>";
        echo "<td>" . ($row['role_name'] ?: 'N/A') . "</td>";
        echo "</tr>";
    }
    echo "</table>";
} else {
    echo "<p>No users found.</p>";
}

// Check user_roles assignments
echo "<h3>4. User Role Assignments:</h3>";
$userRoleAssignmentsQuery = "
    SELECT 
        ur.id,
        ur.user_id,
        ur.role_name,
        ur.is_active,
        u.first_name,
        u.last_name
    FROM user_roles ur
    JOIN users u ON ur.user_id = u.id
    WHERE ur.is_active = 1
    ORDER BY u.first_name, ur.role_name
";
$userRoleAssignmentsResult = $conn->query($userRoleAssignmentsQuery);

if ($userRoleAssignmentsResult && $userRoleAssignmentsResult->num_rows > 0) {
    echo "<table border='1' style='border-collapse: collapse; margin: 10px 0;'>";
    echo "<tr><th>Assignment ID</th><th>User ID</th><th>User Name</th><th>Role Name</th><th>Active</th></tr>";
    while ($row = $userRoleAssignmentsResult->fetch_assoc()) {
        echo "<tr>";
        echo "<td>" . $row['id'] . "</td>";
        echo "<td>" . $row['user_id'] . "</td>";
        echo "<td>" . $row['first_name'] . " " . $row['last_name'] . "</td>";
        echo "<td>" . $row['role_name'] . "</td>";
        echo "<td>" . ($row['is_active'] ? 'Yes' : 'No') . "</td>";
        echo "</tr>";
    }
    echo "</table>";
} else {
    echo "<p>No user role assignments found.</p>";
}

echo "<h3>5. Role Type Mapping Analysis:</h3>";
echo "<div style='background-color: #f0f8ff; padding: 15px; border-radius: 5px;'>";
echo "<p><strong>Expected Role Types in getRoleDashboard():</strong></p>";
echo "<ul>";
echo "<li>teacher</li>";
echo "<li>dean</li>";
echo "<li>librarian</li>";
echo "<li>quality_assurance</li>";
echo "</ul>";
echo "<p><strong>Actual Role Names in Database:</strong></p>";
echo "<p>Check the tables above to see if the role names match the expected types.</p>";
echo "</div>";

$conn->close();
?>
