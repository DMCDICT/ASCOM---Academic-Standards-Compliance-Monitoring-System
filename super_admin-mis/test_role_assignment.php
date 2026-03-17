<?php
require_once 'includes/db_connection.php';

echo "<h2>Role Assignment Test</h2>";

if ($conn->connect_error) {
    echo "<p style='color: red;'>❌ Database connection failed: " . $conn->connect_error . "</p>";
    exit;
}

echo "<p style='color: green;'>✅ Database connected successfully</p>";

// Test 1: Check current user roles
echo "<h3>1. Current User Roles:</h3>";
$query1 = "SELECT id, first_name, last_name, role_id FROM users WHERE is_active = 1 ORDER BY first_name";
$result1 = $conn->query($query1);

if ($result1) {
    echo "<table border='1' style='border-collapse: collapse;'>";
    echo "<tr><th>ID</th><th>Name</th><th>Role ID</th><th>Current Role</th></tr>";
    while ($row = $result1->fetch_assoc()) {
        $roleName = 'Unknown';
        switch ($row['role_id']) {
            case 1: $roleName = 'Super Admin'; break;
            case 2: $roleName = 'Teacher'; break;
            case 3: $roleName = 'Department Dean'; break;
            case 4: $roleName = 'Teacher'; break; // Based on your database
            case 5: $roleName = 'Quality Assurance'; break;
            default: $roleName = 'Role ID: ' . $row['role_id'];
        }
        
        echo "<tr>";
        echo "<td>" . $row['id'] . "</td>";
        echo "<td>" . $row['first_name'] . " " . $row['last_name'] . "</td>";
        echo "<td>" . $row['role_id'] . "</td>";
        echo "<td>" . $roleName . "</td>";
        echo "</tr>";
    }
    echo "</table>";
} else {
    echo "<p style='color: red;'>❌ Error querying users: " . $conn->error . "</p>";
}

// Test 2: Check if we need to create a user_roles table
echo "<h3>2. Checking for user_roles table:</h3>";
$query2 = "SHOW TABLES LIKE 'user_roles'";
$result2 = $conn->query($query2);

if ($result2 && $result2->num_rows > 0) {
    echo "<p style='color: green;'>✅ user_roles table exists</p>";
    
    // Show current user_roles
    $query3 = "SELECT * FROM user_roles";
    $result3 = $conn->query($query3);
    if ($result3) {
        echo "<p>Current user_roles entries:</p>";
        echo "<table border='1' style='border-collapse: collapse;'>";
        echo "<tr><th>User ID</th><th>Role Name</th></tr>";
        while ($row = $result3->fetch_assoc()) {
            echo "<tr>";
            echo "<td>" . $row['user_id'] . "</td>";
            echo "<td>" . $row['role_name'] . "</td>";
            echo "</tr>";
        }
        echo "</table>";
    }
} else {
    echo "<p style='color: orange;'>⚠️ user_roles table does not exist</p>";
    echo "<p>We need to create this table to track multiple roles per user.</p>";
}

// Test 3: Check if we can update user roles directly
echo "<h3>3. Testing Role Update Capability:</h3>";
echo "<p>Current approach: We can update the role_id in the users table.</p>";
echo "<p>Available role IDs: 1 (Super Admin), 2 (Teacher), 3 (Dean), 4 (Teacher), 5 (QA)</p>";

// Test 4: Show available users for assignment
echo "<h3>4. Available Users for Role Assignment:</h3>";
$query4 = "
    SELECT 
        u.id,
        u.first_name,
        u.last_name,
        u.role_id
    FROM users u
    WHERE u.is_active = 1
    AND u.role_id = 4  -- Teachers
    AND u.id NOT IN (
        SELECT DISTINCT dean_user_id 
        FROM departments 
        WHERE dean_user_id IS NOT NULL
    )
    ORDER BY u.first_name, u.last_name
";
$result4 = $conn->query($query4);

if ($result4) {
    echo "<p>Found " . $result4->num_rows . " available users for role assignment:</p>";
    echo "<table border='1' style='border-collapse: collapse;'>";
    echo "<tr><th>ID</th><th>Name</th><th>Current Role</th></tr>";
    while ($row = $result4->fetch_assoc()) {
        echo "<tr>";
        echo "<td>" . $row['id'] . "</td>";
        echo "<td>" . $row['first_name'] . " " . $row['last_name'] . "</td>";
        echo "<td>Teacher (role_id: " . $row['role_id'] . ")</td>";
        echo "</tr>";
    }
    echo "</table>";
} else {
    echo "<p style='color: red;'>❌ Error querying available users: " . $conn->error . "</p>";
}

$conn->close();
?>
