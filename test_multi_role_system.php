<?php
// test_multi_role_system.php
require_once 'super_admin-mis/includes/db_connection.php';

echo "<h2>Multi-Role Login System Test</h2>";

if ($conn->connect_error) {
    echo "<p style='color: red;'>❌ Database connection failed: " . $conn->connect_error . "</p>";
    exit;
}

echo "<p style='color: green;'>✅ Database connected successfully</p>";

// Test 1: Check if user_roles table exists
echo "<h3>1. User Roles Table Status:</h3>";
$tableCheck = $conn->query("SHOW TABLES LIKE 'user_roles'");
if ($tableCheck && $tableCheck->num_rows > 0) {
    echo "<p style='color: green;'>✅ user_roles table exists</p>";
    
    // Show table structure
    $structure = $conn->query("DESCRIBE user_roles");
    if ($structure) {
        echo "<table border='1' style='border-collapse: collapse; margin: 10px 0;'>";
        echo "<tr><th>Field</th><th>Type</th><th>Null</th><th>Key</th><th>Default</th><th>Extra</th></tr>";
        while ($row = $structure->fetch_assoc()) {
            echo "<tr>";
            echo "<td>" . $row['Field'] . "</td>";
            echo "<td>" . $row['Type'] . "</td>";
            echo "<td>" . $row['Null'] . "</td>";
            echo "<td>" . $row['Key'] . "</td>";
            echo "<td>" . $row['Default'] . "</td>";
            echo "<td>" . $row['Extra'] . "</td>";
            echo "</tr>";
        }
        echo "</table>";
    }
} else {
    echo "<p style='color: red;'>❌ user_roles table does not exist</p>";
    exit;
}

// Test 2: Show current user roles
echo "<h3>2. Current User Roles:</h3>";
$userRolesQuery = "
    SELECT 
        ur.id,
        ur.user_id,
        ur.role_name,
        ur.department_id,
        ur.assigned_by,
        ur.assigned_at,
        ur.is_active,
        u.first_name,
        u.last_name,
        u.employee_no,
        d.department_name
    FROM user_roles ur
    JOIN users u ON ur.user_id = u.id
    LEFT JOIN departments d ON ur.department_id = d.id
    WHERE ur.is_active = 1
    ORDER BY u.first_name, ur.role_name
";

$userRolesResult = $conn->query($userRolesQuery);

if ($userRolesResult && $userRolesResult->num_rows > 0) {
    echo "<table border='1' style='border-collapse: collapse; width: 100%;'>";
    echo "<tr style='background-color: #f0f0f0;'>";
    echo "<th>Role ID</th><th>User</th><th>Employee No</th><th>Role</th><th>Department</th><th>Assigned By</th><th>Assigned At</th>";
    echo "</tr>";
    
    while ($row = $userRolesResult->fetch_assoc()) {
        echo "<tr>";
        echo "<td>" . $row['id'] . "</td>";
        echo "<td>" . $row['first_name'] . " " . $row['last_name'] . "</td>";
        echo "<td>" . $row['employee_no'] . "</td>";
        echo "<td>" . $row['role_name'] . "</td>";
        echo "<td>" . ($row['department_name'] ?: 'N/A') . "</td>";
        echo "<td>" . $row['assigned_by'] . "</td>";
        echo "<td>" . $row['assigned_at'] . "</td>";
        echo "</tr>";
    }
    echo "</table>";
} else {
    echo "<p>No user roles found in database.</p>";
}

// Test 3: Show users with multiple roles
echo "<h3>3. Users with Multiple Roles:</h3>";
$multiRoleQuery = "
    SELECT 
        u.id,
        u.first_name,
        u.last_name,
        u.employee_no,
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
    echo "<table border='1' style='border-collapse: collapse; width: 100%;'>";
    echo "<tr style='background-color: #f0f0f0;'>";
    echo "<th>User ID</th><th>Name</th><th>Employee No</th><th>Role Count</th><th>Roles</th>";
    echo "</tr>";
    
    while ($row = $multiRoleResult->fetch_assoc()) {
        echo "<tr>";
        echo "<td>" . $row['id'] . "</td>";
        echo "<td>" . $row['first_name'] . " " . $row['last_name'] . "</td>";
        echo "<td>" . $row['employee_no'] . "</td>";
        echo "<td>" . $row['role_count'] . "</td>";
        echo "<td>" . $row['roles'] . "</td>";
        echo "</tr>";
    }
    echo "</table>";
} else {
    echo "<p>No users with multiple roles found.</p>";
}

// Test 4: Test role assignment API
echo "<h3>4. Test Role Assignment API:</h3>";
echo "<div style='background-color: #f9f9f9; padding: 15px; border-radius: 5px;'>";
echo "<h4>Available Users for Role Assignment:</h4>";

$availableUsersQuery = "
    SELECT 
        u.id,
        u.first_name,
        u.last_name,
        u.employee_no,
        u.role_id,
        d.department_name
    FROM users u
    LEFT JOIN departments d ON u.department_id = d.id
    WHERE u.is_active = 1
    ORDER BY u.first_name, u.last_name
";

$availableUsersResult = $conn->query($availableUsersQuery);

if ($availableUsersResult && $availableUsersResult->num_rows > 0) {
    echo "<table border='1' style='border-collapse: collapse; width: 100%;'>";
    echo "<tr style='background-color: #e0e0e0;'>";
    echo "<th>User ID</th><th>Name</th><th>Employee No</th><th>Current Role ID</th><th>Department</th>";
    echo "</tr>";
    
    while ($row = $availableUsersResult->fetch_assoc()) {
        echo "<tr>";
        echo "<td>" . $row['id'] . "</td>";
        echo "<td>" . $row['first_name'] . " " . $row['last_name'] . "</td>";
        echo "<td>" . $row['employee_no'] . "</td>";
        echo "<td>" . $row['role_id'] . "</td>";
        echo "<td>" . ($row['department_name'] ?: 'N/A') . "</td>";
        echo "</tr>";
    }
    echo "</table>";
}

echo "<h4>Available Departments:</h4>";
$departmentsQuery = "SELECT id, department_code, department_name FROM departments ORDER BY department_name";
$departmentsResult = $conn->query($departmentsQuery);

if ($departmentsResult && $departmentsResult->num_rows > 0) {
    echo "<table border='1' style='border-collapse: collapse; width: 100%;'>";
    echo "<tr style='background-color: #e0e0e0;'>";
    echo "<th>Dept ID</th><th>Code</th><th>Name</th>";
    echo "</tr>";
    
    while ($row = $departmentsResult->fetch_assoc()) {
        echo "<tr>";
        echo "<td>" . $row['id'] . "</td>";
        echo "<td>" . $row['department_code'] . "</td>";
        echo "<td>" . $row['department_name'] . "</td>";
        echo "</tr>";
    }
    echo "</table>";
}

echo "</div>";

// Test 5: Login flow simulation
echo "<h3>5. Login Flow Simulation:</h3>";
echo "<div style='background-color: #e8f5e8; padding: 15px; border-radius: 5px;'>";
echo "<h4>Multi-Role Login Flow:</h4>";
echo "<ol>";
echo "<li>User enters credentials on <code>user_login.php</code></li>";
echo "<li>System validates credentials in <code>user_auth.php</code></li>";
echo "<li>System fetches all user roles from <code>user_roles</code> table</li>";
echo "<li>If user has multiple roles → redirect to <code>role_selection.php</code></li>";
echo "<li>If user has single role → redirect directly to role dashboard</li>";
echo "<li>User selects role and continues to appropriate interface</li>";
echo "</ol>";

echo "<h4>Role Dashboard Mapping:</h4>";
echo "<ul>";
echo "<li><strong>Teacher:</strong> <code>teacher/dashboard.php</code></li>";
echo "<li><strong>Department Dean:</strong> <code>dean/dashboard.php</code></li>";
echo "<li><strong>Librarian:</strong> <code>librarian/dashboard.php</code></li>";
echo "<li><strong>Quality Assurance:</strong> <code>qa/dashboard.php</code></li>";
echo "</ul>";
echo "</div>";

// Test 6: API Endpoints
echo "<h3>6. API Endpoints:</h3>";
echo "<div style='background-color: #f0f8ff; padding: 15px; border-radius: 5px;'>";
echo "<h4>Super Admin Role Management APIs:</h4>";
echo "<ul>";
echo "<li><strong>Assign Role:</strong> <code>POST /super_admin-mis/api/assign_user_role.php</code></li>";
echo "<li><strong>Remove Role:</strong> <code>POST /super_admin-mis/api/remove_user_role.php</code></li>";
echo "<li><strong>Get User Roles:</strong> <code>GET /super_admin-mis/api/get_user_roles.php?user_id=X</code></li>";
echo "</ul>";

echo "<h4>Example API Usage:</h4>";
echo "<pre style='background-color: #f5f5f5; padding: 10px; border-radius: 3px;'>";
echo "// Assign role to user\n";
echo "POST /super_admin-mis/api/assign_user_role.php\n";
echo "Content-Type: application/json\n\n";
echo "{\n";
echo "  \"user_id\": 1,\n";
echo "  \"role_name\": \"dean\",\n";
echo "  \"department_id\": 2\n";
echo "}\n\n";
echo "// Remove role from user\n";
echo "POST /super_admin-mis/api/remove_user_role.php\n";
echo "Content-Type: application/json\n\n";
echo "{\n";
echo "  \"role_id\": 5\n";
echo "}";
echo "</pre>";
echo "</div>";

echo "<h3>7. Next Steps:</h3>";
echo "<div style='background-color: #fff3cd; padding: 15px; border-radius: 5px;'>";
echo "<p><strong>To test the multi-role system:</strong></p>";
echo "<ol>";
echo "<li>Use Super Admin to assign multiple roles to a user</li>";
echo "<li>Login with that user's credentials</li>";
echo "<li>Verify role selection page appears</li>";
echo "<li>Test role switching functionality</li>";
echo "</ol>";
echo "<p><a href='user_login.php' style='background-color: #4CAF50; color: white; padding: 10px 20px; text-decoration: none; border-radius: 5px;'>🚀 Test Multi-Role Login</a></p>";
echo "</div>";

$conn->close();
?>
