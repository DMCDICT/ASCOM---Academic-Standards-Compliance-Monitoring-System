<?php
// test_complete_login_flow.php
require_once 'super_admin-mis/includes/db_connection.php';

echo "<h2>Complete Login Flow Test</h2>";

if ($conn->connect_error) {
    echo "<p style='color: red;'>❌ Database connection failed: " . $conn->connect_error . "</p>";
    exit;
}

echo "<p style='color: green;'>✅ Database connected successfully</p>";

// Test 1: Check current user roles in database
echo "<h3>1. Current User Roles in Database:</h3>";
$userRolesQuery = "
    SELECT 
        ur.id,
        ur.user_id,
        ur.role_name,
        ur.is_active,
        u.first_name,
        u.last_name,
        u.institutional_email
    FROM user_roles ur
    JOIN users u ON ur.user_id = u.id
    WHERE ur.is_active = 1
    ORDER BY u.first_name, ur.role_name
";
$userRolesResult = $conn->query($userRolesQuery);

if ($userRolesResult && $userRolesResult->num_rows > 0) {
    echo "<table border='1' style='border-collapse: collapse; width: 100%;'>";
    echo "<tr style='background-color: #f0f0f0;'>";
    echo "<th>Assignment ID</th><th>User ID</th><th>User Name</th><th>Email</th><th>Role Name</th><th>Active</th>";
    echo "</tr>";
    
    while ($row = $userRolesResult->fetch_assoc()) {
        echo "<tr>";
        echo "<td>" . $row['id'] . "</td>";
        echo "<td>" . $row['user_id'] . "</td>";
        echo "<td>" . $row['first_name'] . " " . $row['last_name'] . "</td>";
        echo "<td>" . $row['institutional_email'] . "</td>";
        echo "<td>" . $row['role_name'] . "</td>";
        echo "<td>" . ($row['is_active'] ? 'Yes' : 'No') . "</td>";
        echo "</tr>";
    }
    echo "</table>";
} else {
    echo "<p>No user role assignments found.</p>";
}

// Test 2: Find users with multiple roles
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
    echo "<table border='1' style='border-collapse: collapse; width: 100%;'>";
    echo "<tr style='background-color: #e8f5e8;'>";
    echo "<th>User ID</th><th>Name</th><th>Email</th><th>Role Count</th><th>Roles</th>";
    echo "</tr>";
    
    while ($row = $multiRoleResult->fetch_assoc()) {
        echo "<tr>";
        echo "<td>" . $row['id'] . "</td>";
        echo "<td>" . $row['first_name'] . " " . $row['last_name'] . "</td>";
        echo "<td>" . $row['institutional_email'] . "</td>";
        echo "<td>" . $row['role_count'] . "</td>";
        echo "<td>" . $row['roles'] . "</td>";
        echo "</tr>";
    }
    echo "</table>";
} else {
    echo "<p>No users with multiple roles found.</p>";
}

// Test 3: Role type mapping verification
echo "<h3>3. Role Type Mapping Verification:</h3>";
echo "<div style='background-color: #f0f8ff; padding: 15px; border-radius: 5px;'>";
echo "<h4>Expected Role Types vs Database Role Names:</h4>";

$expectedRoles = ['teacher', 'dean', 'librarian', 'quality_assurance'];
$dbRoles = [];

$roleNamesQuery = "SELECT DISTINCT role_name FROM user_roles WHERE is_active = 1 ORDER BY role_name";
$roleNamesResult = $conn->query($roleNamesQuery);

if ($roleNamesResult && $roleNamesResult->num_rows > 0) {
    while ($row = $roleNamesResult->fetch_assoc()) {
        $dbRoles[] = $row['role_name'];
    }
}

echo "<table border='1' style='border-collapse: collapse; width: 100%;'>";
echo "<tr style='background-color: #e0e0e0;'>";
echo "<th>Expected Role Type</th><th>Database Role Name</th><th>Status</th>";
echo "</tr>";

foreach ($expectedRoles as $expectedRole) {
    $found = in_array($expectedRole, $dbRoles);
    echo "<tr>";
    echo "<td>" . $expectedRole . "</td>";
    echo "<td>" . ($found ? $expectedRole : 'Not Found') . "</td>";
    echo "<td style='color: " . ($found ? 'green' : 'red') . ";'>" . ($found ? '✅ Found' : '❌ Missing') . "</td>";
    echo "</tr>";
}

echo "</table>";

if (count(array_diff($expectedRoles, $dbRoles)) > 0) {
    echo "<p style='color: orange;'>⚠️ Some expected role types are missing from the database.</p>";
    echo "<p>Missing roles: " . implode(', ', array_diff($expectedRoles, $dbRoles)) . "</p>";
} else {
    echo "<p style='color: green;'>✅ All expected role types are present in the database.</p>";
}

echo "</div>";

// Test 4: Login flow simulation
echo "<h3>4. Login Flow Simulation:</h3>";
echo "<div style='background-color: #fff3cd; padding: 15px; border-radius: 5px;'>";
echo "<h4>Expected Login Flow:</h4>";
echo "<ol>";
echo "<li><strong>User Login Screen</strong> - User enters credentials</li>";
echo "<li><strong>Select Account Role</strong> - If user has multiple roles, they choose which one to access</li>";
echo "<li><strong>Welcome Screen</strong> - Shows success message with role-specific welcome</li>";
echo "<li><strong>Dashboard</strong> - Redirects to the appropriate dashboard</li>";
echo "</ol>";

echo "<h4>File Flow:</h4>";
echo "<ul>";
echo "<li><code>user_login.php</code> → User enters credentials</li>";
echo "<li><code>user_auth.php</code> → Validates credentials and checks roles</li>";
echo "<li><code>role_selection.php</code> → (If multiple roles) User selects role</li>";
echo "<li><code>successful_login.php</code> → Shows welcome message</li>";
echo "<li><code>dashboard.php</code> → User's role-specific dashboard</li>";
echo "</ul>";
echo "</div>";

// Test 5: Dashboard URL mapping
echo "<h3>5. Dashboard URL Mapping:</h3>";
echo "<div style='background-color: #e8f5e8; padding: 15px; border-radius: 5px;'>";
echo "<table border='1' style='border-collapse: collapse; width: 100%;'>";
echo "<tr style='background-color: #d4edda;'>";
echo "<th>Role Type</th><th>Dashboard URL</th><th>Welcome Message</th>";
echo "</tr>";

$dashboardMapping = [
    'teacher' => ['url' => 'teacher/dashboard.php', 'message' => 'Welcome, Teacher!'],
    'dean' => ['url' => 'dean/dashboard.php', 'message' => 'Welcome, Department Dean!'],
    'librarian' => ['url' => 'librarian/dashboard.php', 'message' => 'Welcome, Librarian!'],
    'quality_assurance' => ['url' => 'qa/dashboard.php', 'message' => 'Welcome, Quality Assurance!'],
    'super_admin' => ['url' => 'super_admin-mis/content.php', 'message' => 'Welcome, Super Admin!']
];

foreach ($dashboardMapping as $roleType => $mapping) {
    echo "<tr>";
    echo "<td>" . $roleType . "</td>";
    echo "<td>" . $mapping['url'] . "</td>";
    echo "<td>" . $mapping['message'] . "</td>";
    echo "</tr>";
}

echo "</table>";
echo "</div>";

// Test 6: Test accounts for login
echo "<h3>6. Test Accounts for Login:</h3>";
echo "<div style='background-color: #f8f9fa; padding: 15px; border-radius: 5px;'>";
echo "<p><strong>To test the complete login flow:</strong></p>";

$testUsersQuery = "
    SELECT 
        u.first_name,
        u.last_name,
        u.institutional_email,
        COUNT(ur.id) as role_count,
        GROUP_CONCAT(ur.role_name SEPARATOR ', ') as roles
    FROM users u
    JOIN user_roles ur ON u.id = ur.user_id
    WHERE ur.is_active = 1
    GROUP BY u.id
    ORDER BY role_count DESC, u.first_name
    LIMIT 5
";
$testUsersResult = $conn->query($testUsersQuery);

if ($testUsersResult && $testUsersResult->num_rows > 0) {
    echo "<table border='1' style='border-collapse: collapse; width: 100%;'>";
    echo "<tr style='background-color: #e9ecef;'>";
    echo "<th>Name</th><th>Email</th><th>Role Count</th><th>Roles</th><th>Test Type</th>";
    echo "</tr>";
    
    while ($row = $testUsersResult->fetch_assoc()) {
        $testType = $row['role_count'] > 1 ? 'Multi-Role' : 'Single-Role';
        echo "<tr>";
        echo "<td>" . $row['first_name'] . " " . $row['last_name'] . "</td>";
        echo "<td>" . $row['institutional_email'] . "</td>";
        echo "<td>" . $row['role_count'] . "</td>";
        echo "<td>" . $row['roles'] . "</td>";
        echo "<td>" . $testType . "</td>";
        echo "</tr>";
    }
    echo "</table>";
} else {
    echo "<p>No test users found.</p>";
}

echo "<p><strong>Expected Behavior:</strong></p>";
echo "<ul>";
echo "<li><strong>Single-Role Users:</strong> Login → Welcome Screen → Dashboard</li>";
echo "<li><strong>Multi-Role Users:</strong> Login → Role Selection → Welcome Screen → Dashboard</li>";
echo "</ul>";
echo "</div>";

// Test 7: Action buttons
echo "<h3>7. Test Actions:</h3>";
echo "<div style='text-align: center; margin: 20px 0;'>";
echo "<a href='user_login.php' style='background-color: #4CAF50; color: white; padding: 15px 30px; text-decoration: none; border-radius: 8px; margin: 10px; display: inline-block;'>🚀 Test Login Flow</a>";
echo "<a href='test_login_flow.php' style='background-color: #2196F3; color: white; padding: 15px 30px; text-decoration: none; border-radius: 8px; margin: 10px; display: inline-block;'>🔍 Check Session Data</a>";
echo "<a href='debug_role_types.php' style='background-color: #FF9800; color: white; padding: 15px 30px; text-decoration: none; border-radius: 8px; margin: 10px; display: inline-block;'>📊 View Role Types</a>";
echo "</div>";

$conn->close();
?>
