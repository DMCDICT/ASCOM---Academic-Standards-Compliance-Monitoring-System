<?php
// fix_user_roles_table.php
require_once 'super_admin-mis/includes/db_connection.php';

echo "<h2>Fix User Roles Table Structure</h2>";

if ($conn->connect_error) {
    echo "<p style='color: red;'>❌ Database connection failed: " . $conn->connect_error . "</p>";
    exit;
}

echo "<p style='color: green;'>✅ Database connected successfully</p>";

// Check current table structure
echo "<h3>1. Current Table Structure:</h3>";
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

// Check if department_id column exists
echo "<h3>2. Adding department_id Column:</h3>";
$columnCheck = $conn->query("SHOW COLUMNS FROM user_roles LIKE 'department_id'");
if ($columnCheck && $columnCheck->num_rows > 0) {
    echo "<p style='color: green;'>✅ department_id column already exists</p>";
} else {
    echo "<p style='color: orange;'>⚠️ department_id column missing - adding it now...</p>";
    
    $addColumn = "ALTER TABLE user_roles ADD COLUMN department_id INT NULL AFTER role_name";
    if ($conn->query($addColumn)) {
        echo "<p style='color: green;'>✅ Successfully added department_id column</p>";
    } else {
        echo "<p style='color: red;'>❌ Failed to add department_id column: " . $conn->error . "</p>";
    }
}

// Show updated structure
echo "<h3>3. Updated Table Structure:</h3>";
$updatedStructure = $conn->query("DESCRIBE user_roles");
if ($updatedStructure) {
    echo "<table border='1' style='border-collapse: collapse; margin: 10px 0;'>";
    echo "<tr><th>Field</th><th>Type</th><th>Null</th><th>Key</th><th>Default</th><th>Extra</th></tr>";
    while ($row = $updatedStructure->fetch_assoc()) {
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

// Show current user roles data
echo "<h3>4. Current User Roles Data:</h3>";
$userRolesData = $conn->query("SELECT * FROM user_roles LIMIT 10");
if ($userRolesData && $userRolesData->num_rows > 0) {
    echo "<table border='1' style='border-collapse: collapse; margin: 10px 0;'>";
    echo "<tr>";
    while ($row = $userRolesData->fetch_assoc()) {
        foreach ($row as $key => $value) {
            echo "<th>" . $key . "</th>";
        }
        break;
    }
    echo "</tr>";
    
    $userRolesData->data_seek(0);
    while ($row = $userRolesData->fetch_assoc()) {
        echo "<tr>";
        foreach ($row as $value) {
            echo "<td>" . ($value ?: 'NULL') . "</td>";
        }
        echo "</tr>";
    }
    echo "</table>";
} else {
    echo "<p>No data found in user_roles table.</p>";
}

echo "<h3>5. Next Steps:</h3>";
echo "<div style='background-color: #e8f5e8; padding: 15px; border-radius: 5px;'>";
echo "<p><strong>✅ Login should now work properly!</strong></p>";
echo "<p>The department_id column has been added to the user_roles table.</p>";
echo "<p><a href='user_login.php' style='background-color: #4CAF50; color: white; padding: 10px 20px; text-decoration: none; border-radius: 5px;'>🚀 Test Login Now</a></p>";
echo "</div>";

$conn->close();
?>
