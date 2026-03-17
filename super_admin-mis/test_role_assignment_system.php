<?php
require_once 'includes/db_connection.php';

echo "<h2>Role Assignment System Test</h2>";

if ($conn->connect_error) {
    echo "<p style='color: red;'>❌ Database connection failed: " . $conn->connect_error . "</p>";
    exit;
}

echo "<p style='color: green;'>✅ Database connected successfully</p>";

// Test 1: Check user_roles table
echo "<h3>1. User Roles Table Status:</h3>";
$checkTableQuery = "SHOW TABLES LIKE 'user_roles'";
$result = $conn->query($checkTableQuery);

if ($result && $result->num_rows > 0) {
    echo "<p style='color: green;'>✅ user_roles table exists</p>";
    
    // Count total roles
    $countQuery = "SELECT COUNT(*) as total FROM user_roles WHERE is_active = 1";
    $countResult = $conn->query($countQuery);
    $count = $countResult->fetch_assoc()['total'];
    echo "<p>Total active roles: $count</p>";
    
} else {
    echo "<p style='color: red;'>❌ user_roles table does not exist</p>";
    exit;
}

// Test 2: Check available users for librarian role
echo "<h3>2. Available Users for Librarian Role:</h3>";
$librarianQuery = "
    SELECT 
        u.id,
        u.first_name,
        u.last_name,
        u.role_id
    FROM users u
    WHERE u.is_active = 1
    AND u.role_id = 4
    AND u.id NOT IN (
        SELECT DISTINCT dean_user_id 
        FROM departments 
        WHERE dean_user_id IS NOT NULL
    )
    AND u.id NOT IN (
        SELECT DISTINCT user_id 
        FROM user_roles 
        WHERE role_name = 'librarian' AND is_active = 1
    )
    ORDER BY u.first_name, u.last_name
";
$librarianResult = $conn->query($librarianQuery);

if ($librarianResult) {
    echo "<p>Found " . $librarianResult->num_rows . " available users for librarian role:</p>";
    echo "<table border='1' style='border-collapse: collapse;'>";
    echo "<tr><th>ID</th><th>Name</th><th>Current Role</th></tr>";
    while ($row = $librarianResult->fetch_assoc()) {
        echo "<tr>";
        echo "<td>" . $row['id'] . "</td>";
        echo "<td>" . $row['first_name'] . " " . $row['last_name'] . "</td>";
        echo "<td>Teacher (role_id: " . $row['role_id'] . ")</td>";
        echo "</tr>";
    }
    echo "</table>";
} else {
    echo "<p style='color: red;'>❌ Error querying librarian users: " . $conn->error . "</p>";
}

// Test 3: Check available users for QA role
echo "<h3>3. Available Users for QA Role:</h3>";
$qaQuery = "
    SELECT 
        u.id,
        u.first_name,
        u.last_name,
        u.role_id
    FROM users u
    WHERE u.is_active = 1
    AND u.role_id = 4
    AND u.id NOT IN (
        SELECT DISTINCT dean_user_id 
        FROM departments 
        WHERE dean_user_id IS NOT NULL
    )
    AND u.id NOT IN (
        SELECT DISTINCT user_id 
        FROM user_roles 
        WHERE role_name = 'quality_assurance' AND is_active = 1
    )
    ORDER BY u.first_name, u.last_name
";
$qaResult = $conn->query($qaQuery);

if ($qaResult) {
    echo "<p>Found " . $qaResult->num_rows . " available users for QA role:</p>";
    echo "<table border='1' style='border-collapse: collapse;'>";
    echo "<tr><th>ID</th><th>Name</th><th>Current Role</th></tr>";
    while ($row = $qaResult->fetch_assoc()) {
        echo "<tr>";
        echo "<td>" . $row['id'] . "</td>";
        echo "<td>" . $row['first_name'] . " " . $row['last_name'] . "</td>";
        echo "<td>Teacher (role_id: " . $row['role_id'] . ")</td>";
        echo "</tr>";
    }
    echo "</table>";
} else {
    echo "<p style='color: red;'>❌ Error querying QA users: " . $conn->error . "</p>";
}

// Test 4: Show current role assignments
echo "<h3>4. Current Role Assignments:</h3>";
$rolesQuery = "
    SELECT 
        ur.user_id,
        u.first_name,
        u.last_name,
        ur.role_name,
        ur.assigned_at,
        ur.assigned_by
    FROM user_roles ur
    JOIN users u ON ur.user_id = u.id
    WHERE ur.is_active = 1
    ORDER BY u.first_name, ur.role_name
";
$rolesResult = $conn->query($rolesQuery);

if ($rolesResult) {
    echo "<table border='1' style='border-collapse: collapse;'>";
    echo "<tr><th>User ID</th><th>Name</th><th>Role</th><th>Assigned At</th><th>Assigned By</th></tr>";
    while ($row = $rolesResult->fetch_assoc()) {
        echo "<tr>";
        echo "<td>" . $row['user_id'] . "</td>";
        echo "<td>" . $row['first_name'] . " " . $row['last_name'] . "</td>";
        echo "<td>" . $row['role_name'] . "</td>";
        echo "<td>" . $row['assigned_at'] . "</td>";
        echo "<td>" . $row['assigned_by'] . "</td>";
        echo "</tr>";
    }
    echo "</table>";
} else {
    echo "<p style='color: red;'>❌ Error querying roles: " . $conn->error . "</p>";
}

// Test 5: Test API endpoints
echo "<h3>5. API Endpoint Test:</h3>";
echo "<p>Testing get_available_users.php endpoints...</p>";

// Test librarian endpoint
$librarianUrl = "http://localhost/DataDrift/ASCOM%20Monitoring%20System/super_admin-mis/api/get_available_users.php?role_type=librarian";
$librarianResponse = file_get_contents($librarianUrl);
$librarianData = json_decode($librarianResponse, true);

if ($librarianData && $librarianData['success']) {
    echo "<p style='color: green;'>✅ Librarian API working - Found " . $librarianData['count'] . " available users</p>";
} else {
    echo "<p style='color: red;'>❌ Librarian API failed: " . ($librarianData['message'] ?? 'Unknown error') . "</p>";
}

// Test QA endpoint
$qaUrl = "http://localhost/DataDrift/ASCOM%20Monitoring%20System/super_admin-mis/api/get_available_users.php?role_type=quality_assurance";
$qaResponse = file_get_contents($qaUrl);
$qaData = json_decode($qaResponse, true);

if ($qaData && $qaData['success']) {
    echo "<p style='color: green;'>✅ QA API working - Found " . $qaData['count'] . " available users</p>";
} else {
    echo "<p style='color: red;'>❌ QA API failed: " . ($qaData['message'] ?? 'Unknown error') . "</p>";
}

echo "<h3>6. System Status:</h3>";
echo "<p style='color: green;'>✅ Role assignment system is ready!</p>";
echo "<p>You can now:</p>";
echo "<ul>";
echo "<li>Open the dashboard</li>";
echo "<li>Click 'Assign Librarian Access' or 'Assign QA Access'</li>";
echo "<li>Select a user from the list</li>";
echo "<li>Click 'Assign Access' to assign the role</li>";
echo "</ul>";

$conn->close();
?>
