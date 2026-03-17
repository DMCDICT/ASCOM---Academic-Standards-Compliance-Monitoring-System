<?php
require_once 'includes/db_connection.php';

echo "<h2>Database User Test</h2>";

if ($conn->connect_error) {
    echo "<p style='color: red;'>❌ Database connection failed: " . $conn->connect_error . "</p>";
    exit;
}

echo "<p style='color: green;'>✅ Database connected successfully</p>";

// Test 1: Check all users
echo "<h3>1. All Users in Database:</h3>";
$query1 = "SELECT id, first_name, last_name, role_id, is_active, department_id FROM users";
$result1 = $conn->query($query1);

if ($result1) {
    echo "<table border='1' style='border-collapse: collapse;'>";
    echo "<tr><th>ID</th><th>Name</th><th>Role ID</th><th>Active</th><th>Dept ID</th></tr>";
    while ($row = $result1->fetch_assoc()) {
        echo "<tr>";
        echo "<td>" . $row['id'] . "</td>";
        echo "<td>" . $row['first_name'] . " " . $row['last_name'] . "</td>";
        echo "<td>" . $row['role_id'] . "</td>";
        echo "<td>" . ($row['is_active'] ? 'Yes' : 'No') . "</td>";
        echo "<td>" . $row['department_id'] . "</td>";
        echo "</tr>";
    }
    echo "</table>";
} else {
    echo "<p style='color: red;'>❌ Error querying users: " . $conn->error . "</p>";
}

// Test 2: Check active users
echo "<h3>2. Active Users Only:</h3>";
$query2 = "SELECT id, first_name, last_name, role_id FROM users WHERE is_active = 1";
$result2 = $conn->query($query2);

if ($result2) {
    echo "<p>Found " . $result2->num_rows . " active users</p>";
    echo "<table border='1' style='border-collapse: collapse;'>";
    echo "<tr><th>ID</th><th>Name</th><th>Role ID</th></tr>";
    while ($row = $result2->fetch_assoc()) {
        echo "<tr>";
        echo "<td>" . $row['id'] . "</td>";
        echo "<td>" . $row['first_name'] . " " . $row['last_name'] . "</td>";
        echo "<td>" . $row['role_id'] . "</td>";
        echo "</tr>";
    }
    echo "</table>";
} else {
    echo "<p style='color: red;'>❌ Error querying active users: " . $conn->error . "</p>";
}

// Test 3: Check users with role_id = 2
echo "<h3>3. Users with Role ID = 2:</h3>";
$query3 = "SELECT id, first_name, last_name, role_id FROM users WHERE role_id = 2";
$result3 = $conn->query($query3);

if ($result3) {
    echo "<p>Found " . $result3->num_rows . " users with role_id = 2</p>";
    echo "<table border='1' style='border-collapse: collapse;'>";
    echo "<tr><th>ID</th><th>Name</th><th>Role ID</th></tr>";
    while ($row = $result3->fetch_assoc()) {
        echo "<tr>";
        echo "<td>" . $row['id'] . "</td>";
        echo "<td>" . $row['first_name'] . " " . $row['last_name'] . "</td>";
        echo "<td>" . $row['role_id'] . "</td>";
        echo "</tr>";
    }
    echo "</table>";
} else {
    echo "<p style='color: red;'>❌ Error querying role_id = 2: " . $conn->error . "</p>";
}

// Test 4: Check department deans
echo "<h3>4. Current Department Deans:</h3>";
$query4 = "SELECT d.id, d.department_name, d.dean_user_id, u.first_name, u.last_name 
           FROM departments d 
           LEFT JOIN users u ON d.dean_user_id = u.id 
           WHERE d.dean_user_id IS NOT NULL";
$result4 = $conn->query($query4);

if ($result4) {
    echo "<p>Found " . $result4->num_rows . " departments with deans</p>";
    echo "<table border='1' style='border-collapse: collapse;'>";
    echo "<tr><th>Dept ID</th><th>Department</th><th>Dean User ID</th><th>Dean Name</th></tr>";
    while ($row = $result4->fetch_assoc()) {
        echo "<tr>";
        echo "<td>" . $row['id'] . "</td>";
        echo "<td>" . $row['department_name'] . "</td>";
        echo "<td>" . $row['dean_user_id'] . "</td>";
        echo "<td>" . ($row['first_name'] ? $row['first_name'] . " " . $row['last_name'] : 'N/A') . "</td>";
        echo "</tr>";
    }
    echo "</table>";
} else {
    echo "<p style='color: red;'>❌ Error querying deans: " . $conn->error . "</p>";
}

// Test 5: Check what role IDs exist
echo "<h3>5. Available Role IDs:</h3>";
$query5 = "SELECT DISTINCT role_id, COUNT(*) as count FROM users GROUP BY role_id ORDER BY role_id";
$result5 = $conn->query($query5);

if ($result5) {
    echo "<table border='1' style='border-collapse: collapse;'>";
    echo "<tr><th>Role ID</th><th>Count</th></tr>";
    while ($row = $result5->fetch_assoc()) {
        echo "<tr>";
        echo "<td>" . $row['role_id'] . "</td>";
        echo "<td>" . $row['count'] . "</td>";
        echo "</tr>";
    }
    echo "</table>";
} else {
    echo "<p style='color: red;'>❌ Error querying role IDs: " . $conn->error . "</p>";
}

$conn->close();
?>
